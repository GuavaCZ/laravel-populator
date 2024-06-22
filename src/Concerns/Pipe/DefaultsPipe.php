<?php

namespace Guava\LaravelPopulator\Concerns\Pipe;

use Illuminate\Support\Collection;

trait DefaultsPipe
{
    /**
     * Adds default values for attributes that are not set.
     *
     * @param  Collection<string, scalar>  $data
     * @return Collection<string, scalar>
     */
    public function defaults(Collection $data): Collection
    {
        return $data
            ->when(
                $this->bundle->model->usesTimestamps(),
                fn (Collection $collection) => $collection->merge([
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            )
            ->when(
                fn () => ! empty($this->bundle->defaults),
                fn (Collection $collection) => $collection->merge(
                    collect($this->bundle->defaults)
                        ->filter(fn ($item, $key) => ! $data->has($key))
                        ->map(function ($value) {
                            return is_callable($value) ? $value() : $value;
                        })
                )
            )
        ;
    }
}
