<?php

namespace Guava\LaravelPopulator\Concerns\Pipe;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

trait MutatorsPipe
{
    /**
     * Mutes the attributes of the model using the defined mutators.
     *
     * @param Collection $data
     * @return Collection
     */
    public function mutate(Collection $data): Collection
    {
        return $data
            ->when(fn() => !empty($this->bundle->mutators),
                fn(Collection $collection) => $collection->map(function ($value, $key) {
                    return Arr::exists($this->bundle->mutators, $key) ? $this->bundle->mutators[$key]($value) : $value;
                }));
    }
}
