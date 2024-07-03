<?php

namespace Guava\LaravelPopulator\Support\Processors;

use Closure;
use Guava\LaravelPopulator\Contracts\InteractsWithPipeline;
use Guava\LaravelPopulator\Processor;
use Illuminate\Support\Collection;

abstract class PipelineInvoker implements InteractsWithPipeline
{
    protected ?Closure $usingPipes = null;

    /**
     * Invokes the pipeline by piping the data through it
     *
     * @param  Collection<string, scalar>  $data
     */
    public function processPipeline(Processor $processor, Collection $data): void
    {
        $data->pipeThrough($this->pipes($processor));
    }

    /**
     * Provides the list of pipes by a closure, receiving the current Processor
     * as a parameter
     *
     * @param  Closure(Processor):array<Closure(Collection<string, scalar>):Collection<string, scalar>>  $pipes
     * @return $this
     */
    public function usingPipes(Closure $pipes): static
    {
        $this->usingPipes = $pipes;

        return $this;
    }

    /**
     * Provides a list of closures to pipe the data through
     *
     * @return array<Closure(Collection<string, scalar>):Collection<string, scalar>>
     */
    protected function pipes(Processor $processor): array
    {
        if ($pipe = $this->usingPipes) {
            return $pipe($processor);
        }

        return $this->defaultPipes($processor);
    }

    /**
     * A list of default closures to pipe the data through
     *
     * @return array<Closure(Collection<string, scalar>):Collection<string, scalar>>
     */
    abstract public function defaultPipes(Processor $processor): array;
}
