<?php

namespace Guava\LaravelPopulator\Concerns\Bundle;

use Closure;

trait HasMutators
{
    /**
     * @var array<string, Closure(scalar):scalar>
     */
    public array $mutators = [];

    /**
     * Mutates the specified attribute using the given callback.
     *
     * @param  string  $attribute  Attribute to mutate.
     * @param  Closure(scalar):scalar  $closure  Callback to run on the attribute.
     * @return $this
     */
    public function mutate(string $attribute, Closure $closure): static
    {
        $this->mutators[$attribute] = $closure;

        return $this;
    }
}
