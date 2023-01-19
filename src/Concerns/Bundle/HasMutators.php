<?php

namespace Guava\LaravelPopulator\Concerns\Bundle;

use Closure;

trait HasMutators
{

    public array $mutators = [];

    /**
     * Mutates the specified attribute using the given callback.
     *
     * @param string $attribute Attribute to mutate.
     * @param Closure $closure Callback to run on the attribute.
     * @return $this
     */
    public function mutate(string $attribute, Closure $closure): static
    {
        $this->mutators[$attribute] = $closure;

        return $this;
    }
}
