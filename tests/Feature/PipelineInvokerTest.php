<?php

namespace Tests\Feature;

use Guava\LaravelPopulator\Bundle;
use Guava\LaravelPopulator\Contracts\InteractsWithPipeline;
use Guava\LaravelPopulator\Populator;
use Guava\LaravelPopulator\Processor;
use Guava\LaravelPopulator\Support\Processors\InsertPipelineInvoker;
use Tests\Fixtures\TestUser;
use Tests\TestCase;

class PipelineInvokerTest extends TestCase
{
    public function test_ability_to_swap_pipeline()
    {
        $this->app->bind(InteractsWithPipeline::class, fn () => (new InsertPipelineInvoker())->usingPipes(
            fn (Processor $processor) => [$processor->insert(...)]
        ));

        Populator::make('initial')
            ->bundles([
                Bundle::make(TestUser::class),
            ])
            ->call();

        $this->assertNotNull($user = TestUser::whereEmail('foo@example.com')->sole());

        $user->delete();

        $this->app->bind(InteractsWithPipeline::class, fn () => (new InsertPipelineInvoker())->usingPipes(
            fn (Processor $processor) => []
        ));

        Populator::make('initial')
            ->bundles([
                Bundle::make(TestUser::class),
            ])
            ->call();

        $this->assertFalse(TestUser::whereEmail('foo@example.com')->exists());
    }
}
