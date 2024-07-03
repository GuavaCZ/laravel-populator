<?php

namespace Tests\Feature\Concerns\Pipe;

use Guava\LaravelPopulator\Bundle;
use Guava\LaravelPopulator\Contracts\InteractsWithBundleInsert;
use Guava\LaravelPopulator\Populator;
use Guava\LaravelPopulator\Processor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Fixtures\NullPipelineInvoker;
use Tests\Fixtures\TestUser;
use Tests\TestCase;

class InsertPipeTest extends TestCase
{
    use RefreshDatabase;

    public function testInsert(): void
    {
        Populator::make('initial')
            ->pipeableUsing((new NullPipelineInvoker())->usingPipes(
                fn (Processor $processor) => [$processor->insert(...)]
            ))
            ->bundles([
                Bundle::make(TestUser::class),
            ])
            ->call()
        ;

        $this->assertTrue(TestUser::whereEmail('foo@example.com')->exists());
    }

    public function testInsertUsing(): void
    {
        Populator::make('initial')
            ->pipeableUsing((new NullPipelineInvoker())->usingPipes(
                fn (Processor $processor) => [$processor
                    ->performInsertUsing(new class implements InteractsWithBundleInsert
                    {
                        public function insertDataFromBundle(array $data, Bundle $bundle): int | string
                        {
                            return $bundle->model::unguarded(fn () => $bundle->model::create($data)->getKey());
                        }
                    })
                    ->insert(...)]
            ))
            ->bundles([
                Bundle::make(TestUser::class),
            ])
            ->call()
        ;

        $this->assertTrue(TestUser::whereEmail('foo@example.com')->exists());
    }

    public function testInsertNonIncrementingId(): void
    {
        Populator::make('initial')
            ->pipeableUsing((new NullPipelineInvoker())->usingPipes(
                fn (Processor $processor) => [$processor->insert(...)]
            ))
            ->bundles([
                Bundle::make(TestUser::class),
            ])
            ->call()
        ;

        $this->assertTrue(TestUser::whereEmail('foo@example.com')->exists());
    }
}
