<?php

namespace Guava\LaravelPopulator\Concerns\Bundle;

use Closure;

trait HasGenerators
{
    /**
     * @var array<string, Closure(array<string, scalar>):scalar>
     */
    public array $generators = [];

    /**
     * Adds a generated attribute to the record.
     *
     * @param  string  $attribute  Name of the attribute.
     * @param  Closure(array<string, scalar>):scalar  $closure  Callback to run on the attribute.
     * @return $this
     */
    public function generate(string $attribute, Closure $closure): static
    {
        $this->generators[$attribute] = $closure;

        return $this;
    }
}
