<?php

namespace Guava\LaravelPopulator\Concerns;

trait HasEnvironments
{
    public array $environments = [];

    public function environments(array $environments): static
    {
        $this->environments = $environments;

        return $this;
    }

    public function getEnvironments(): array
    {
        return $this->environments;
    }

    public function checkEnvironment(): bool
    {
        return empty($this->getEnvironments()) || app()->environment($this->getEnvironments());
    }

}
