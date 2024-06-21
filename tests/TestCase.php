<?php

namespace Tests;

use Guava\LaravelPopulator\PopulatorServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function todo(): void
    {
        $caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1];
        self::markTestIncomplete(sprintf('Todo: %s::%s', $caller['class'], $caller['function']));
    }

    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (
                string $modelName,
            ) => 'Guava\\LaravelPopulator\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            PopulatorServiceProvider::class,
        ];
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/Fixtures/database');
    }

    public function defineEnvironment($app): void
    {
        $app->useDatabasePath(__DIR__.'/Fixtures/database');
    }
}
