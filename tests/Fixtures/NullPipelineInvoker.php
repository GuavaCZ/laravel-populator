<?php

namespace Tests\Fixtures;

use Closure;
use Guava\LaravelPopulator\Concerns\HasData;
use Guava\LaravelPopulator\Processor;
use Guava\LaravelPopulator\Support\Processors\PipelineInvoker;

class NullPipelineInvoker extends PipelineInvoker
{
    use HasData;

    protected Closure $pipeline;

    public function defaultPipes(Processor $processor): array
    {
        return [];
    }
}
