<?php

namespace Guava\LaravelPopulator\Concerns\Bundle;

trait HasDefaults
{

    public array $defaults = [];

    /**
     * Adds default data to the specified attribute.
     *
     * @param string $attribute Attribute for default data.
     * @param mixed $closure Callback to run on the attribute.
     * @return $this
     */
    public function default(string $attribute, mixed $closure): static
    {
        $this->defaults[$attribute] = $closure;

        return $this;
    }
}
