<?php

namespace Guava\LaravelPopulator\Concerns\Pipe;

use Illuminate\Support\Collection;

trait GeneratorsPipe
{

    /**
     * Adds default values for attributes that are not set.
     *
     * @param Collection $data
     * @return Collection
     */
    protected function generators(Collection $data): Collection
    {
        return $data
            ->when(fn() => !empty($this->bundle->generators),
                fn(Collection $collection) => $collection->merge(
                    collect($this->bundle->generators)
                        ->filter(fn($item, $key) => !$data->has($key))
                        ->map(fn ($value) => app()->call($value, [
                            'attributes' => $data->toArray()
                        ]))
                ));
    }
}
