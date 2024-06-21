<?php

namespace Guava\LaravelPopulator\Support\Processors;

use Closure;
use Guava\LaravelPopulator\Concerns\Pipe;
use Guava\LaravelPopulator\Processor;

class InsertPipelineInvoker extends PipelineInvoker
{

    function defaultPipes(Processor $processor): array
    {
        return [
            $processor->relations(...),
            $processor->defaults(...),
            $processor->mutate(...),
            $processor->generators(...),
            $processor->insert(...),
            $processor->related(...),
        ];
    }
}