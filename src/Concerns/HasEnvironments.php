<?php

namespace Guava\LaravelPopulator\Concerns;

trait HasEnvironments
{
    /**
     * @var string[]
     */
    public array $environments = [];

    /**
     * Sets the allowed environments.
     *
     * @param  string[]  $environments
     * @return $this
     */
    public function environments(array $environments): static
    {
        $this->environments = $environments;

        return $this;
    }

    /**
     * Returns the allowed environments.
     *
     * @return string[]
     */
    public function getEnvironments(): array
    {
        return $this->environments;
    }

    /**
     * Checks whether the environment is valid.
     */
    public function checkEnvironment(): bool
    {
        return empty($this->getEnvironments()) || app()->environment($this->getEnvironments());
    }
}
