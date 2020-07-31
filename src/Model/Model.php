<?php

namespace Shortio\Laravel\Model;

use ArrayAccess;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use JsonSerializable;
use Shortio\Laravel\Api\ApiInterface;
use Shortio\Laravel\Facades\Shortio;
use Shortio\Laravel\Model\Concerns\GuardsAttributes;
use Shortio\Laravel\Model\Concerns\HasAttributes;
use Shortio\Laravel\Model\Concerns\HasEvents;
use Shortio\Laravel\Model\Concerns\HidesAttributes;
use Shortio\Laravel\Model\Exceptions\MassAssignmentException;


abstract class Model implements ArrayAccess, JsonSerializable
{

    use GuardsAttributes, HasAttributes, HasEvents, HidesAttributes;

    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    const CREATED_AT = 'createdAt';
    /**
     * The name of the "updated at" column.
     *
     * @var string
     */

    const UPDATED_AT = 'updatedAt';
    /**
     * The event dispatcher instance.
     *
     * @var \Illuminate\Contracts\Events\Dispatcher
     */
    protected static $dispatcher;
    /**
     * The array of booted models.
     *
     * @var array
     */
    protected static $booted = [];
    /**
     * Indicates if the model exists.
     *
     * @var bool
     */

    /**
     * The array of trait initializers that will be called on each new instance.
     *
     * @var array
     */
    protected static $traitInitializers = [];
    /**
     * Indicates if the model exists.
     *
     * @var bool
     */
    public $exists = false;
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';
    /**
     * @var ApiInterface
     */
    protected $api;

    /**
     * Create a new Eloquent model instance.
     *
     * @param  array  $attributes
     *
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        $this->bootIfNotBooted();
        $this->syncOriginal();

        $this->fill($attributes);
    }

    /**
     * Check if the model needs to be booted and if so, do it.
     *
     * @return void
     */
    protected function bootIfNotBooted()
    {
        if ( ! isset(static::$booted[static::class])) {
            static::$booted[static::class] = true;

            $this->fireModelEvent('booting', false);

            static::booting();
            static::boot();
            static::booted();

            $this->fireModelEvent('booted', false);
        }
    }

    /**
     * Perform any actions required before the model boots.
     *
     * @return void
     */
    protected static function booting()
    {
        //
    }

    /**
     * Bootstrap the model and its traits.
     *
     * @return void
     */
    protected static function boot()
    {
        static::bootTraits();
    }

    /**
     * Boot all of the bootable traits on the model.
     *
     * @return void
     */
    protected static function bootTraits()
    {
        $class = static::class;

        $booted = [];

        static::$traitInitializers[$class] = [];

        foreach (class_uses_recursive($class) as $trait) {
            $method = 'boot'.class_basename($trait);

            if (method_exists($class, $method) && ! in_array($method, $booted)) {
                forward_static_call([$class, $method]);

                $booted[] = $method;
            }

            if (method_exists($class, $method = 'initialize'.class_basename($trait))) {
                static::$traitInitializers[$class][] = $method;

                static::$traitInitializers[$class] = array_unique(
                    static::$traitInitializers[$class]
                );
            }
        }
    }

    /**
     * Perform any actions required after the model boots.
     *
     * @return void
     */
    protected static function booted()
    {
        //
    }

    /**
     * Fill the model with an array of attributes.
     *
     * @param  array  $attributes
     *
     * @return $this
     *
     * @throws \Illuminate\Database\Eloquent\MassAssignmentException
     */
    public function fill(array $attributes)
    {
        $totallyGuarded = $this->totallyGuarded();

        foreach ($this->fillableFromArray($attributes) as $key => $value) {
            $key = $this->removeTableFromKey($key);

            // The developers may choose to place some attributes in the "fillable" array
            // which means only those attributes may be set through mass assignment to
            // the model, and all others will just get ignored for security reasons.
            if ($this->isFillable($key)) {
                $this->setAttribute($key, $value);
            } elseif ($totallyGuarded) {
                throw new MassAssignmentException(
                    sprintf(
                        'This [%s] property is not allowed in mass assignment on [%s].',
                        $key,
                        get_class($this)
                    )
                );
            }
        }

        return $this;
    }

    /**
     * Remove the table name from a given key.
     *
     * @param  string  $key
     *
     * @return string
     */
    protected function removeTableFromKey($key)
    {
        return Str::contains($key, '.') ? last(explode('.', $key)) : $key;
    }

    /**
     * Destroy the models for the given IDs.
     *
     * @param  \Illuminate\Support\Collection|array|int|string  $ids
     *
     * @return int
     */
    public static function destroy($ids)
    {
        // We'll initialize a count here so we will return the total number of deletes
        // for the operation. The developers can then check this number as a boolean
        // type value or get this total count of records deleted for logging, etc.
        $count = 0;

        if ($ids instanceof BaseCollection) {
            $ids = $ids->all();
        }

        $ids = is_array($ids) ? $ids : func_get_args();

        // We will actually pull the models from the database table and call delete on
        // each of them individually so that their events get fired properly with a
        // correct set of attributes in case the developers wants to check these.
        $key = ($instance = new static)->getKeyName();

        $api   = $instance->getApi();
        $count = collect($ids)->reduce(
            function ($count, $id) use ($api) {
                if ($api->delete($id)) {
                    return $count + 1;
                }

                return $count;
            }
        );

        return $count;
    }

    /**
     * Get the primary key for the model.
     *
     * @return string
     */
    public function getKeyName()
    {
        return $this->primaryKey;
    }

    public function getApi()
    {
        if ($this->api === null) {
            $this->setApi($this->prepareApi());
        }

        return $this->api;
    }

    /**
     * @param  mixed  $api
     *
     * @return Model
     */
    public function setApi($api)
    {
        $this->api = $api;

        return $this;
    }

    /**
     * @return ApiInterface
     */
    public function prepareApi()
    {
        $method   = Str::snake(Str::pluralStudly(class_basename($this)));
        $ApiClass = Shortio::$method();

        return $ApiClass;
    }

    public static function find($id)
    {
        return collect(static::all())->filter(
            function ($instance) use ($id) {
                return $id == $instance->id;
            }
        )->first();
    }

    static public function all()
    {
        $i = (new static)->newInstance();

        return Cache::remember(
            static::class.'@all',
            config("shortio.cache.timeout"),
            function () use ($i) {
                return $i->hydrate(
                    $i->getApi()->all()
                )->all();
            }
        );
    }

    /**
     * Create a new instance of the given model.
     *
     * @param  array  $attributes
     * @param  bool  $exists
     *
     * @return static
     */
    public function newInstance($attributes = [], $exists = false)
    {
        // This method just provides a convenient way for us to generate fresh model
        // instances of this current model. It is particularly useful during the
        // hydration of new objects via the Eloquent query builder instances.
        $model = new static((array)$attributes);

        $model->exists = $exists;

        $model->mergeCasts($this->casts);

        return $model;
    }

    /**
     * Create a collection of models from plain arrays.
     *
     * @param  array  $items
     *
     * @return \Illuminate\Support\Collection
     */
    public function hydrate(array $items)
    {
        $instance = $this->newInstance();

        return $instance->newCollection(
            array_map(
                function ($item) use ($instance) {
                    return $instance->newFromBuilder($item);
                },
                $items
            )
        );
    }

    /**
     * Create a new Eloquent Collection instance.
     *
     * @param  array  $models
     *
     * @return \Illuminate\Support\Collection
     */
    public function newCollection(array $models = [])
    {
        return collect($models);
    }

    /**
     * Create a new model instance that is existing.
     *
     * @param  array  $attributes
     * @param  string|null  $connection
     *
     * @return static
     */
    public function newFromBuilder($attributes = [])
    {
        $model = $this->newInstance([], true);

        $model->setRawAttributes((array)$attributes, true);

        $model->fireModelEvent('retrieved', false);

        return $model;
    }

    /**
     * Get the name of the "created at" column.
     *
     * @return string
     */
    public function getCreatedAtColumn()
    {
        return static::CREATED_AT;
    }

    /**
     * Get the name of the "updated at" column.
     *
     * @return string
     */
    public function getUpdatedAtColumn()
    {
        return static::UPDATED_AT;
    }

    /**
     * Get the value for a given offset.
     *
     * @param  string  $offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->getAttribute($offset);
    }

    /**
     * Set the value at the given offset.
     *
     * @param  string  $offset
     * @param  mixed  $value
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->setAttribute($offset, $value);
    }

    /**
     * Determine if an attribute exists on the model.
     *
     * @param  string  $key
     *
     * @return bool
     */
    public function __isset($key)
    {
        return $this->offsetExists($key);
    }

    /**
     * Determine if the given offset exists.
     *
     * @param  string  $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return ! is_null($this->getAttribute($offset));
    }

    /**
     * Convert the model instance to an array.
     *
     * @return array
     */
    public function toArray()
    {
        return array_merge($this->attributesToArray(), $this->relationsToArray());
    }

    public function relationsToArray()
    {
        return [];
    }

    /**
     * Dynamically retrieve attributes on the model.
     *
     * @param  string  $key
     *
     * @return mixed
     */
    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    /**
     * Dynamically set attributes on the model.
     *
     * @param  string  $key
     * @param  mixed  $value
     *
     * @return void
     */
    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
    }

    /**
     * Unset an attribute on the model.
     *
     * @param  string  $key
     *
     * @return void
     */
    public function __unset($key)
    {
        $this->offsetUnset($key);
    }

    /**
     * Unset the value at the given offset.
     *
     * @param  string  $offset
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->attributes[$offset]);
    }

    /**
     * Convert the model to its string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * Convert the model instance to JSON.
     *
     * @param  int  $options
     *
     * @return string
     *
     * @throws \Illuminate\Database\Eloquent\JsonEncodingException
     */
    public function toJson($options = 0)
    {
        $json = json_encode($this->jsonSerialize(), $options);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw JsonEncodingException::forModel($this, json_last_error_msg());
        }

        return $json;
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        $attributes = $this->getAttributes();

        return $attributes;
    }

    /**
     * Save the model to the database.
     *
     * @param  array  $options
     *
     * @return bool
     */
    public function save(array $options = [])
    {
        // If the "saving" event returns false we'll bail out of the save and return
        // false, indicating that the save failed. This provides a chance for any
        // listeners to cancel save operations if validations fail or whatever.
        if ($this->fireModelEvent('saving') === false) {
            return false;
        }

        // If the model already exists we can just update our record
        // that is already in short.io using the current IDs. Otherwise, we'll
        // just insert them.

        if ($this->exists) {
            $saved = $this->isDirty() ?
                $this->performUpdate($this->getApi()) : true;
        }

        // If the model is brand new, we'll insert it into our database and set the
        // ID attribute on the model to the value of the newly inserted row's ID
        // which is typically an auto-increment value managed by the database.
        else {
            $saved = $this->performInsert($this->getApi());
        }

        // If the model is successfully saved, we need to do a few more things once
        // that is done. We will call the "saved" method here to run any actions
        // we need to happen after a model gets successfully saved right here.
        if ($saved) {
            $this->finishSave($options);
        }

        return $saved;
    }

    /**
     * Perform a model update operation.
     *
     * @return bool
     */
    protected function performUpdate(ApiInterface $api)
    {
        // If the updating event returns false, we will cancel the update operation so
        // developers can hook Validation systems into their models and cancel this
        // operation if the model does not pass validation. Otherwise, we update.
        if ($this->fireModelEvent('updating') === false) {
            return false;
        }


        // Once we have run the update operation, we will fire the "updated" event for
        // this model instance. This will allow developers to hook into these after
        // models are updated, giving them a chance to do any special processing.
        $dirty = $this->getDirty();

        if (count($dirty) > 0) {
            $api->update($this->id, $dirty);

            $this->syncChanges();

            $this->fireModelEvent('updated', false);
        }

        return true;
    }

    /**
     * Perform a model insert operation.
     *
     * @return bool
     */
    protected function performInsert(ApiInterface $api)
    {
        if ($this->fireModelEvent('creating') === false) {
            return false;
        }

        $attributes = $this->getAttributes();

        if (empty($attributes)) {
            return true;
        }

        $data = $api->save($attributes);
        $this->fill($data);

        // We will go ahead and set the exists property to true, so that it is set when
        // the created event is fired, just in case the developer tries to update it
        // during the event. This will allow them to do so and run an update here.
        $this->exists = true;

        $this->wasRecentlyCreated = true;

        $this->fireModelEvent('created', false);

        return true;
    }

    /**
     * Perform any actions that are necessary after the model is saved.
     *
     * @param  array  $options
     *
     * @return void
     */
    protected function finishSave(array $options)
    {
        $this->fireModelEvent('saved', false);

        if ($this->isDirty() && ($options['touch'] ?? true)) {
            $this->touchOwners();
        }

        $this->syncOriginal();
    }

    /**
     * Touch the owning relations of the model.
     *
     * @return void
     */
    public function touchOwners()
    {
    }

    /**
     * Force a hard delete on a soft deleted model.
     *
     * This method protects developers from running forceDelete when trait is missing.
     *
     * @return bool|null
     */
    public function forceDelete()
    {
        return $this->delete();
    }

    /**
     * Delete the model from the database.
     *
     * @return bool|null
     *
     * @throws \Exception
     */
    public function delete()
    {
        $this->mergeAttributesFromClassCasts();

        if (is_null($this->getKeyName())) {
            throw new Exception('No primary key defined on model.');
        }

        // If the model doesn't exist, there is nothing to delete so we'll just return
        // immediately and not do anything else. Otherwise, we will continue with a
        // deletion process on the model, firing the proper events, and so forth.
        if ( ! $this->exists) {
            return;
        }

        if ($this->fireModelEvent('deleting') === false) {
            return false;
        }

        // Here, we'll touch the owning models, verifying these timestamps get updated
        // for the models. This will allow any caching to get broken on the parents
        // by the timestamp. Then we will go ahead and delete the model instance.
//        $this->touchOwners();

        $this->getApi()->delete($this->getAttribute($this->getKeyName()));
        $this->exists = false;


        // Once the model has been deleted, we will fire off the deleted event so that
        // the developers may hook into post-delete operations. We will then return
        // a boolean true as the delete is presumably successful on the database.
        $this->fireModelEvent('deleted', false);

        return true;
    }

    /**
     * @param  string|array  $column
     * @param  null  $operator
     * @param  null  $value
     */
    public function where($column, $value = null)
    {
        if (is_array($column)) {
            foreach ($column as $key => $value) {
                $this->getApi()->addQueryString($key, $value);
            }
        } elseif (is_string($column)) {
            $this->getApi()->addQueryString($column, $value);
        }

        return $this;
    }

    public function get($path = null)
    {
        $cache_key = static::class.'@get';
        if ($path) {
            $cache_key .= '/'.$path;
        }
        if ($this->getApi()->hasQueryString()) {
            $cache_key .= '/'.base64_encode(serialize($this->getApi()->getQueryString()));
        }
        $api = $this->getApi();

        return Cache::remember(
            $cache_key,
            config('shortio.cache.timeout'),
            function () use ($api, $path) {
                return $api->get($path);
            }
        );
    }

    /**
     * Initialize any initializable traits on the model.
     *
     * @return void
     */
    protected function initializeTraits()
    {
        foreach (static::$traitInitializers[static::class] as $method) {
            $this->{$method}();
        }
    }

    /**
     * Determine if the given relation is loaded.
     *
     * @param  string  $key
     * @return bool
     */
    public function relationLoaded($key)
    {
        return true;
    }


}
