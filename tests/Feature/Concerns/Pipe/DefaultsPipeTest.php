<?php

namespace Tests\Feature\Concerns\Pipe;

use Guava\LaravelPopulator\Bundle;
use Guava\LaravelPopulator\Concerns\Pipe\DefaultsPipe;
use Guava\LaravelPopulator\Populator;
use Guava\LaravelPopulator\Processor;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Tests\Fixtures\NullPipelineInvoker;
use Tests\Fixtures\TestUser;
use Tests\TestCase;

#[CoversClass(DefaultsPipe::class)]
#[UsesClass(Populator::class)]
class DefaultsPipeTest extends TestCase
{
    public function test_defaults()
    {
        $populator = Populator::make('initial')
            ->pipeableUsing((new NullPipelineInvoker())->usingPipes(
                fn (Processor $processor) => [
                    $processor->defaults(...),
                    function (Collection $collection) {
                        $this->assertEquals('bar', $collection->get('foo'));
                    },
                ]
            ))
            ->bundles([
                Bundle::make(TestUser::class)
                    ->default('foo', 'bar'),
            ]);
        $populator->call();

    }
}
