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
            ) => 'Tests\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );

        copy(__DIR__.'/../config/config.php', config_path('populator.php'));
        //        copy(__DIR__.'/../database/migrations', database_path());

    }

    #[\Override]
    protected function tearDown(): void
    {
        parent::tearDown();
        if (file_exists($path = config_path('populator.php'))) {
            unlink($path);
        }
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
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    public function defineEnvironment($app): void
    {
        $app->useDatabasePath(__DIR__.'/Fixtures/database');
    }
}
