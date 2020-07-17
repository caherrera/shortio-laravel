<?php


namespace Shortio\Laravel\Facades;


use Illuminate\Support\Facades\Facade;
use Shortio\Laravel\ConnectorInterface;

class Shortio extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return ConnectorInterface::class;
    }
}