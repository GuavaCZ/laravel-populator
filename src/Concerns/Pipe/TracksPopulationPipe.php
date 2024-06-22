<?php

namespace Guava\LaravelPopulator\Concerns\Pipe;

use Guava\LaravelPopulator\Contracts\TracksPopulatedEntries;
use Guava\LaravelPopulator\Models\Population;
use Guava\LaravelPopulator\Processor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * @mixin Processor
 */
trait TracksPopulationPipe
{
    /**
     * Creates Population records for all models inserted by the Populator by enumerating the tracked
     * entries in the Memory data store
     *
     * @param  Collection<string, scalar>  $data
     * @return Collection<string, scalar>
     */
    public function track(Collection $data): Collection
    {
        $model = $this->bundle->model;
        if ($model instanceof TracksPopulatedEntries && static::hasTrackingFeature()) {
            assert($model instanceof Model);
            $processor = $this->bundle->populator->getName();
            foreach (data_get($this->bundle->populator->memory->all(), $model::class) as $key => $id) {
                Population::insert([
                    'key' => $key,
                    'populator' => $processor,
                    'bundle' => $this->bundle->getName(),
                    'populatable_id' => $id,
                    'populatable_type' => $model->getMorphClass(),
                ]);
            }
        }

        return $data;
    }
}
