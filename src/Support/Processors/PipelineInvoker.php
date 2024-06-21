<?php

namespace Guava\LaravelPopulator\Support\Processors;

use Closure;
use Guava\LaravelPopulator\Concerns\HasData;
use Guava\LaravelPopulator\Contracts\InteractsWithPipeline;
use Guava\LaravelPopulator\Processor;
use Illuminate\Support\Collection;

abstract class PipelineInvoker implements InteractsWithPipeline
{

    protected ?Closure $usingPipes = null;

    public function processPipeline(Processor $processor, Collection $data): void
    {
        $data->pipeThrough($this->pipes($processor));
    }

    public function usingPipes(Closure $pipes): static
    {
        $this->usingPipes = $pipes;
        return $this;
    }

    protected function pipes(Processor $processor): array
    {
        if($pipe = $this->usingPipes) {
            return $pipe($processor);
        }
        return $this->defaultPipes($processor);
    }

    abstract function defaultPipes(Processor $processor): array;

}