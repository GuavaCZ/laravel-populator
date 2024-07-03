<?php

namespace Guava\LaravelPopulator\Support\Processors;

use Closure;
use Guava\LaravelPopulator\Processor;
use Illuminate\Support\Collection;

/**
 * Pipeline that handles inserting the records into the database
 */
class InsertPipelineInvoker extends PipelineInvoker
{
    /**
     * @return array<Closure(Collection<string, scalar>):Collection<string, scalar>>
     */
    public function defaultPipes(Processor $processor): array
    {
        return [
            $processor->relations(...),
            $processor->defaults(...),
            $processor->mutate(...),
            $processor->generators(...),
            $processor->insert(...),
            $processor->track(...),
            $processor->related(...),
        ];
    }
}
