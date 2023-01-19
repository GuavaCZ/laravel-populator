<?php

namespace Guava\LaravelPopulator\Concerns;

trait HasEnvironments
{
    public array $environments = [];

    /**
     * Sets the allowed environments.
     *
     * @param array $environments
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
     * @return array
     */
    public function getEnvironments(): array
    {
        return $this->environments;
    }

    /**
     * Checks whether the environment is valid.
     *
     * @return bool
     */
    public function checkEnvironment(): bool
    {
        return empty($this->getEnvironments()) || app()->environment($this->getEnvironments());
    }

}
