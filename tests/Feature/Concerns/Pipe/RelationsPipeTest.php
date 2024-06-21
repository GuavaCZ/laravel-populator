<?php

namespace Tests\Feature\Concerns\Pipe;

use Guava\LaravelPopulator\Bundle;
use Guava\LaravelPopulator\Populator;
use Guava\LaravelPopulator\Processor;
use Illuminate\Support\Collection;
use Tests\Fixtures\NullPipelineInvoker;
use Tests\Fixtures\TestPost;
use Tests\TestCase;

class RelationsPipeTest extends TestCase
{
    public function test_relations()
    {

        $populator = Populator::make('initial')
            ->pipeableUsing((new NullPipelineInvoker())->usingPipes(
                fn (Processor $processor) => [
                    $processor->relations(...),
                    function (Collection $collection) {
                        $this->assertEquals(1, $collection->get('user_id'));
                    },
                ]
            ))
            ->bundles([
                Bundle::make(TestPost::class)
                    ->makeProcessorUsing(function (Bundle $bundle) {
                        $processor = \Mockery::mock(
                            Processor::class,
                            [$bundle]
                        )
                            ->makePartial();

                        return $processor
                            ->shouldAllowMockingProtectedMethods()
                            ->shouldReceive('getPrimaryId')->once()->andReturn(1)
                            ->getMock();
                    })
                    ->records([
                        'post-one' => [
                            'owner' => 'email:foo@example.com',
                            'content' => 'test',
                        ],
                    ]),
            ]);
        $populator->call();

    }
}
