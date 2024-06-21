<?php

namespace Tests\Feature\Concerns\Pipe;

use Guava\LaravelPopulator\Bundle;
use Guava\LaravelPopulator\Populator;
use Guava\LaravelPopulator\Processor;
use Tests\Fixtures\NullPipelineInvoker;
use Tests\Fixtures\TestUser;
use Tests\TestCase;

class InsertPipeTest extends TestCase
{
    public function test_insert()
    {
        Populator::make('initial')
            ->pipeableUsing((new NullPipelineInvoker())->usingPipes(
                fn (Processor $processor) => [$processor->insert(...)]
            ))
            ->bundles([
                Bundle::make(TestUser::class),
            ])
            ->call();

        $this->assertTrue(TestUser::whereEmail('foo@example.com')->exists());
    }

    public function test_insert_non_incrementing_id()
    {
        Populator::make('initial')
            ->pipeableUsing((new NullPipelineInvoker())->usingPipes(
                fn (Processor $processor) => [$processor->insert(...)]
            ))
            ->bundles([
                Bundle::make(TestUser::class),
            ])
            ->call();

        $this->assertTrue(TestUser::whereEmail('foo@example.com')->exists());
    }
}
