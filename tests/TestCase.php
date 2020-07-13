<?php

namespace AMBERSIVE\Tests;

use Illuminate\Contracts\Console\Kernel;

use Orchestra\Testbench\TestCase as Orchestra;

use AMBERSIVE\Api\ApiServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            ApiServiceProvider::class,
            \Tymon\JWTAuth\Providers\LaravelServiceProvider::class,
            \Spatie\Permission\PermissionServiceProvider::class,
            \PragmaRX\Yaml\Package\ServiceProvider::class,
            \PragmaRX\Version\Package\ServiceProvider::class
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Yaml' => 'PragmaRX\Yaml\Package\Facade',
            'Version' => 'PragmaRX\Version\Package\Facade'
        ];
    }
    
}
