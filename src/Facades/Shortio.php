<?php


namespace Shortio\Laravel\Facades;


use Illuminate\Support\Facades\Facade;
use Shortio\Laravel\Api\Domain;


/**
 * Class Shortio
 *
 * @package Shortio\Laravel\Facades
 * @method Domain domains();
 */
class Shortio extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return 'shortio';
    }
}