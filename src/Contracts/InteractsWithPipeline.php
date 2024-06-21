<?php

namespace Guava\LaravelPopulator\Contracts;

use Closure;
use Guava\LaravelPopulator\Processor;
use Illuminate\Support\Collection;

interface InteractsWithPipeline
{
    public function processPipeline(Processor $processor, Collection $data): void;
}