<?php

namespace Guava\LaravelPopulator\Concerns;

use Illuminate\Support\Collection;

trait HasData
{
    /**
     * @var Collection<string, scalar>
     */
    protected Collection $data;
}
