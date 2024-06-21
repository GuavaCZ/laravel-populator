<?php

namespace Guava\LaravelPopulator\Concerns\Pipe;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

trait InsertPipe
{
    /**
     * Inserts the model into the database.
     *
     * @param Collection $data
     * @return Collection
     */
    public function insert(Collection $data): Collection
    {
        $id = DB::table($this->bundle->table)
            ->insertGetId($data->toArray());

        // Get the ID if it's not auto incrementing
        if (!$this->bundle->model->getIncrementing()) {
            $id = $data->get($this->bundle->model->getKeyName());
        }
        $this->bundle->populator->memory->set($this->bundle->model::class, $this->name, $id);

        return $data;
    }
}
