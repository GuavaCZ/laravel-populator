<?php

namespace Guava\LaravelPopulator\Population;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Processor
{

    protected Sample $sample;

    protected \SplFileInfo $file;
    protected Collection $data;
    protected string $name;

    protected Memory $memory;


    public function process(\SplFileInfo $file): void
    {
        $this->file = $file;
        $this->data = collect(include $file->getPathname());
        $this->name = str($file->getFilename())
            ->beforeLast('.')
            ->toString();


        $this->data->pipeThrough([
            $this->relations(...),
            $this->mutate(...),
            $this->insert(...),
            $this->related(...),
        ]);
    }

    protected function relations(Collection $data): Collection
    {
        Log::info($data->get('relations', 'NO RELATIONS'));
        if (!$data->has('relations')) {
            return $data;
        }

        if (empty($data->get('relations', []))) {
            return $data;
        }

        return $data
            ->pipe(fn($collection) => $collection
                ->except(['relations'])
                ->mergeRecursive(
                    collect($data->get('relations'))
                        ->mapWithKeys(function ($value, $relationName) {
                            $relation = $this->sample->model->$relationName();

                            if ($relation instanceof BelongsTo) {
                                return $this->belongsTo($relation, $value);
                            }
                            if ($relation instanceof BelongsToMany) {
                                $this->belongsToMany($relation, $value);
                                return [];
                            }

                            throw new \Exception('Invalid relation configuration');
                        })
                ));
    }

    protected function belongsTo(BelongsTo $relation, string $value): array
    {
//        dd($relation);

        $id = $this->sample->populator->memory->get($relation->getRelated()::class, $value);

        return [$relation->getForeignKeyName() => $id];
    }

    protected function belongsToMany(BelongsToMany $relation, array $value): void
    {
        foreach ($value as $identifier) {
            $this->memory->set($relation->getTable(), $identifier, [
                'foreign' => [
                    'pivot_key' => $relation->getForeignPivotKeyName()
                ],
                'related' => [
                    'pivot_key' => $relation->getRelatedPivotKeyName(),
                    'id' => $this->sample->populator->memory->get($relation->getRelated()::class, $identifier),
                ],
            ]);
        }

//        dd($this->memory);
    }

    protected function mutate(Collection $data): Collection
    {
        return $data
            ->when($this->sample->model->usesTimestamps(),
                fn(Collection $collection) => $collection->merge([
                    'created_at' => now(),
                    'updated_at' => now(),
                ]))
            ->when(fn() => !empty($this->sample->mutators),
                fn(Collection $collection) => $collection->map(function ($value, $key) {
                    return Arr::exists($this->sample->mutators, $key) ? $this->sample->mutators[$key]($value) : $value;
                }));
    }

    protected function insert(Collection $data): Collection
    {
        $id = DB::table($this->sample->table)
            ->insertGetId($data->toArray());

        $this->sample->populator->memory->set($this->sample->model::class, $this->name, $id);

        return $data;
    }

    protected function related(Collection $data): Collection {
        $id = $this->sample->populator->memory->get($this->sample->model::class, $this->name);

        foreach ($this->memory->all() as $table => $relations) {

            foreach ($relations as $relation) {
                DB::table($table)
                    ->insert([
                        $relation['foreign']['pivot_key'] => $id,
                        $relation['related']['pivot_key'] => $relation['related']['id'],
                    ]);
            }

        }

        return $data;
    }

    private function __construct(Sample $sample)
    {
        $this->sample = $sample;
        $this->memory = new Memory();
    }

    public static function make(Sample $sample): static
    {
        return new static($sample);
    }

}
