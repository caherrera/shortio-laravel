<?php

namespace Shortio\Laravel\Tests;

use Illuminate\Support\Facades\Hash;
use Orchestra\Testbench\Concerns\CreatesApplication;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Shortio\Laravel\ShortioServiceProvider;

class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function getPackageProviders($app)
    {
        return [
            ShortioServiceProvider::class,
        ];
    }

    /**
     * Define the environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function getEnvironmentSetup($app)
    {
        $config = $app['config'];

        // Laravel database setup.
        $config->set('database.default', 'testbench');
        $config->set(
            'database.connections.testbench',
            [
                'driver'   => 'sqlite',
                'database' => ':memory:',
                'prefix'   => '',
            ]
        );
    }


}
