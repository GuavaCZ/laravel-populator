<?php

namespace Tests\Feature\Concerns\Pipe;

use Guava\LaravelPopulator\Bundle;
use Guava\LaravelPopulator\Populator;
use Guava\LaravelPopulator\Processor;
use Illuminate\Support\Collection;
use Tests\Fixtures\NullPipelineInvoker;
use Tests\Fixtures\TestUser;
use Tests\TestCase;

class GeneratorsPipeTest extends TestCase
{
    public function testGenerators(): void
    {
        $populator = Populator::make('initial')
            ->pipeableUsing((new NullPipelineInvoker())->usingPipes(
                fn (Processor $processor) => [
                    $processor->generators(...),
                    function (Collection $collection) {
                        $this->assertEquals('bar', $collection->get('foo'));
                    },
                ]
            ))
            ->bundles([
                Bundle::make(TestUser::class)
                    ->generate('foo', fn () => 'bar'),
            ])
        ;
        $populator->call();

    }
}
