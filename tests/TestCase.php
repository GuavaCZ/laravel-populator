<?php

namespace Tests;

use Guava\LaravelPopulator\PopulatorServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            PopulatorServiceProvider::class,
        ];
    }
}
