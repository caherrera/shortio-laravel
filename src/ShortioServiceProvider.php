<?php

namespace Shortio\Laravel;

use Illuminate\Container\Container;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Shortio\Laravel\Api\Shortio;

class ShortioServiceProvider extends ServiceProvider
{
    /**
     * We'll defer loading this service provider so our
     * LDAP connection isn't instantiated unless
     * requested to speed up our application.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Run service provider boot operations.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->isLumen()) {
            return;
        }

        if ($this->app->runningInConsole()) {
            $config = __DIR__.'/Config/config.php';

            $this->publishes(
                [
                    $config => config_path('shortio.php'),
                ]
            );
        }
    }

    /**
     * Determines if the current application is a Lumen instance.
     *
     * @return bool
     */
    protected function isLumen()
    {
        return Str::contains($this->app->version(), 'Lumen');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // Bind the contract to the object
        // in the IoC for dependency injection.
        $this->app->singleton(
            Shortio::class,
            function (Container $app) {
                $config = $app->make('config')->get('shortio');

                // Verify configuration exists.
                if (is_null($config)) {
                    $message = 'ShortIO configuration could not be found. Try re-publishing using `php artisan vendor:publish`.';

                    throw new \RuntimeException($message);
                }

                return new Shortio($config);
            }
        );
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [Shortio::class];
    }

    /**
     * Determines whether logging is enabled.
     *
     * @return bool
     */
    protected function isLogging()
    {
        return Config::get('shortio.logging', false);
    }
}
