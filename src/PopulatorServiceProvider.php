<?php

namespace Guava\LaravelPopulator;

use Guava\LaravelPopulator\Console\MakePopulatorCommand;
use Guava\LaravelPopulator\Contracts\InteractsWithPipeline;
use Guava\LaravelPopulator\Support\Processors\InsertPipelineInvoker;
use Illuminate\Support\ServiceProvider;

class PopulatorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'populator');
        $this->app->bind(InteractsWithPipeline::class, InsertPipelineInvoker::class);
        $this->app->scoped(Features::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('populator.php'),
            ], 'config');

            $this->publishes([
                __DIR__.'/../database/migrations/' => database_path('migrations'),
            ], 'migrations');

            $this->loadMigrationsFrom([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ]);

            $this->commands([
                MakePopulatorCommand::class,
            ]);
        }
    }
}
