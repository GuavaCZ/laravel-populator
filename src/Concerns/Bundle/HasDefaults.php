<?php

namespace Guava\LaravelPopulator\Concerns\Bundle;

use Closure;

trait HasDefaults
{
    /**
     * @var array<string, scalar|Closure>
     */
    public array $defaults = [];

    /**
     * Adds a default attribute to the record.
     *
     * @param  string  $attribute  Name of the attribute.
     * @param  mixed  $closure  Callback to run on the attribute.
     * @return $this
     */
    public function default(string $attribute, mixed $closure): static
    {
        $this->defaults[$attribute] = $closure;

        return $this;
    }
}
