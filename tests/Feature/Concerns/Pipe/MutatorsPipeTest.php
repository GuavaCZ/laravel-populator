<?php

namespace Tests\Feature\Concerns\Pipe;

use Guava\LaravelPopulator\Bundle;
use Guava\LaravelPopulator\Populator;
use Guava\LaravelPopulator\Processor;
use Illuminate\Support\Collection;
use Tests\Fixtures\NullPipelineInvoker;
use Tests\Fixtures\TestUser;
use Tests\TestCase;

class MutatorsPipeTest extends TestCase
{
    public function test_mutate()
    {
        $populator = Populator::make('initial')
            ->pipeableUsing((new NullPipelineInvoker())->usingPipes(
                fn (Processor $processor) => [
                    $processor->mutate(...),
                    function (Collection $collection) {
                        $this->assertEquals('FOO@EXAMPLE.COM', $collection->get('email'));
                    },
                ]
            ))
            ->bundles([
                Bundle::make(TestUser::class)
                    ->mutate('email', fn ($value) => strtoupper($value)),
            ]);
        $populator->call();

    }
}
