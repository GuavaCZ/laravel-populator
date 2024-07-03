<?php

namespace Guava\LaravelPopulator\Concerns;

use Guava\LaravelPopulator\Contracts\InteractsWithPipeline;

trait HasPipeline
{
    protected ?InteractsWithPipeline $pipeable = null;

    public function pipeableUsing(?InteractsWithPipeline $withPipeline): static
    {
        if ($withPipeline !== null) {
            $this->pipeable = $withPipeline;
        }

        return $this;
    }

    public function getPipeable(): ?InteractsWithPipeline
    {
        return $this->pipeable;
    }
}
