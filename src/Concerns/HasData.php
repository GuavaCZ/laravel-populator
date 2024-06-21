<?php

namespace Guava\LaravelPopulator\Concerns;

use Illuminate\Support\Collection;

trait HasData
{
    protected Collection $data;

    public function getData(): Collection
    {
        return $this->data;
    }
}