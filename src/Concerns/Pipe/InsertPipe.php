<?php

namespace Guava\LaravelPopulator\Concerns\Pipe;

use Closure;
use Guava\LaravelPopulator\Bundle;
use Guava\LaravelPopulator\Contracts\InteractsWithBundleInsert;
use Guava\LaravelPopulator\Exceptions\InvalidBundleException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

trait InsertPipe
{
    protected ?InteractsWithBundleInsert $performInsertUsing = null;

    /**
     * Inserts the model into the database.
     *
     * @param  Collection<string, scalar>  $data
     * @return Collection<string, scalar>
     *
     * @throws InvalidBundleException
     */
    public function insert(Collection $data): Collection
    {
        $id = $this->insertBundle($data->toArray(), $this->bundle);
        throw_unless($id, InvalidBundleException::class, 'insertBundle cannot return a blank value');
        $this->bundle->populator->memory->set($this->bundle->model::class, $this->name, $id);

        return $data;
    }

    /**
     * @param  array<string, scalar>  $data
     */
    public function insertBundle(array $data, Bundle $bundle): int | string
    {
        if ($this->performInsertUsing) {
            return $this->performInsertUsing->insertDataFromBundle($data, $bundle);
        }
        $id = DB::table($bundle->table)
            ->insertGetId($data)
        ;

        // Get the ID if it's not auto incrementing
        if (! $bundle->model->getIncrementing()) {
            return data_get($data, $bundle->model->getKeyName());
        }

        return $id;
    }

    /**
     * @param  Closure(array<string, scalar>,Bundle):(int|string)|InteractsWithBundleInsert|null  $withBundleInsert
     * @return $this
     */
    public function performInsertUsing(null | Closure | InteractsWithBundleInsert $withBundleInsert): static
    {
        if ($withBundleInsert) {
            $this->performInsertUsing = $withBundleInsert instanceof InteractsWithBundleInsert ?
                $withBundleInsert :
                new readonly class($withBundleInsert) implements InteractsWithBundleInsert
                {
                    public function __construct(
                        protected Closure $insert
                    ) {}

                    /**
                     * @param  array<string,scalar>  $data
                     */
                    public function insertDataFromBundle(array $data, Bundle $bundle): int | string
                    {
                        return forward_static_call($this->insert, $data, $bundle);
                    }
                };
        }

        return $this;
    }
}
