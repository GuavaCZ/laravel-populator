<?php

namespace Guava\LaravelPopulator\Concerns\Pipe;

use Guava\LaravelPopulator\Bundle;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\MorphOneOrMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

trait RelatedPipe
{

    /**
     * Handles the queued related models and inserts them into the database.
     *
     * @param Collection $data
     * @return Collection
     */
    protected function related(Collection $data): Collection
    {
        $id = $this->bundle->populator->memory->get($this->bundle->model::class, $this->name);

        foreach ($this->memory->all() as $table => $relations) {

            foreach ($relations as $name => $relation) {

                if ($relation['relation'] === HasOneOrMany::class) {
                    $bundle = Bundle::make($relation['related']);
                    $bundle->populator = $this->bundle->populator;

                    $processor = new static($bundle);
                    $processor->process($relation['record'], $name);
                }

                if ($relation['relation'] === MorphToMany::class) {
                    DB::table($table)
                        ->insert([
                            $relation['foreign']['pivot_key'] => $id,
                            $relation['foreign']['morph_type'] => $this->bundle->model::class,
                            $relation['related']['pivot_key'] => $relation['related']['id'],
                        ]);
                }

                if ($relation['relation'] === BelongsToMany::class) {
                    DB::table($table)
                        ->insert([
                            $relation['foreign']['pivot_key'] => $id,
                            $relation['related']['pivot_key'] => $relation['related']['id'],
                        ]);
                }

                if ($relation['relation'] === MorphOneOrMany::class) {
                    $bundle = Bundle::make($relation['related']);
                    $bundle->populator = $this->bundle->populator;

                    $processor = new static($bundle);
                    $processor->process($relation['record'], $name);
                }
            }

        }

        return $data;
    }
}
