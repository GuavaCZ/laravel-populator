<?php

namespace Guava\LaravelPopulator\Contracts;

use Guava\LaravelPopulator\Processor;
use Illuminate\Support\Collection;

interface InteractsWithPipeline
{
    /**
     * Invokes the pipeline by piping the data through it
     *
     * @param  Collection<string, scalar>  $data
     */
    public function processPipeline(Processor $processor, Collection $data): void;
}
