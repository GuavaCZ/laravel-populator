<?php

namespace Guava\LaravelPopulator\Population;

use Guava\LaravelPopulator\Exceptions\InvalidSampleException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * The processor is responsible for processing the samples.
 */
class Processor
{
    protected Sample $sample;

    protected \SplFileInfo $file;
    protected Collection $data;
    protected string $name;

    protected Memory $memory;


    /**
     * Processes the passed data through a set of pipelines.
     *
     * @param array|Collection $data The data to process.
     * @param string $name Name of the current 'process'.
     * @return void
     */
    public function process(array|Collection $data, string $name): void
    {
        $this->data = is_array($data) ? collect($data) : $data;
        $this->name = $name;

        $this->data->pipeThrough([
            $this->relations(...),
            $this->mutate(...),
            $this->insert(...),
            $this->related(...),
        ]);
    }

    /**
     * Parses the relations defined on the model and processes the supported relations.
     *
     * @param Collection $data
     * @return Collection
     * @throws InvalidSampleException
     */
    protected function relations(Collection $data): Collection
    {
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
                        ->mapWithKeys(function ($value, $relationName) use ($data) {
                            $relation = $this->sample->model->$relationName();


                            if ($relation instanceof MorphTo) {
                                return $this->morphTo($relation, $value);
                            }

                            if ($relation instanceof MorphMany) {
                                $this->morphMany($relation, $value);
                                return [];
                            }

                            if ($relation instanceof BelongsTo) {
                                return $this->belongsTo($relation, $value);
                            }

                            if ($relation instanceof BelongsToMany) {
                                $this->belongsToMany($relation, $value);
                                return [];
                            }

                            throw new InvalidSampleException('Relations are misconfigured in ' . $this->file->getFilename());
                        })
                ));
    }

    /**
     * Processes the belongs to relationship and sets the foreign key.
     *
     * @param BelongsTo $relation
     * @param string $value
     * @return array
     */
    protected function belongsTo(BelongsTo $relation, string $value): array
    {
        // Load from memory
        $id = $this->sample->populator->memory->get($relation->getRelated()::class, $value);

        if (!$id) {
            // Load by ID
            if (is_numeric($value)) {
                $id = $value;
                // Load
            } else {
                [$key, $val] = explode(':', $value, 2);
                $id = DB::table($relation->getRelated()->getTable())->where($key, $val)->first()->id;
            }
        }

        return [$relation->getForeignKeyName() => $id];
    }

    /**
     * Processes the belongs to many relationship and queues the relation for creation.
     *
     * @param BelongsToMany $relation
     * @param array $value
     * @return void
     */
    protected function belongsToMany(BelongsToMany $relation, array $value): void
    {
        foreach ($value as $identifier) {
//            dump($relation->getRelated()::class, $identifier, $this->sample->populator->memory->get($relation->getRelated()::class, $identifier));
            $this->memory->set($relation->getTable(), $identifier, [
                'relation' => 'belongsToMany',
                'foreign' => [
                    'pivot_key' => $relation->getForeignPivotKeyName()
                ],
                'related' => [
                    'pivot_key' => $relation->getRelatedPivotKeyName(),
                    'id' => $this->sample->populator->memory->get($relation->getRelated()::class, $identifier),
                ],
            ]);
        }
    }

    /**
     * Processes the belongs to relationship and sets the foreign key.
     *
     * @param MorphTo $relation
     * @param array $value
     * @return array
     */
    protected function morphTo(MorphTo $relation, array $value): array
    {
        $id = $this->sample->populator->memory->get($value[1], $value[0]);

        return [$relation->getForeignKeyName() => $id, $relation->getMorphType() => $value[1]];
    }


    /**
     * Processes the belongs to relationship and sets the foreign key.
     *
     * @param MorphMany $relation
     * @param string $value
     * @return array
     */
    protected function morphMany(MorphMany $relation, array|int $value): void
    {
//        $relation->getRelated()->getTable()
//        dd($relation);


        if (is_int($value)) {
            $value = Collection::times($value, fn() => []);
        }

        $index = 0;
        foreach ($value as $unit) {
//            $processor = new Processor($sample);

            $morphName = Str::beforeLast($relation->getForeignKeyName(), '_');
            $unit = collect($unit)->mergeRecursive([
                'relations' => [
                    $morphName => [$this->name, $relation->getMorphClass()],
                ]
            ])->toArray();
//            dd($unit);

//            dd($processor->process($unit));

            $otherMorphName = Str::before($relation->getQualifiedForeignKeyName(), '.');
            $this->memory->set($relation->getRelated()->getTable(), "{$this->name}_{$otherMorphName}_{$index}", [
                'relation' => 'morphMany',
                'related' => $relation->getRelated()::class,
                'model' => $unit,
//                'morph' => [
//                    'related' => $relation->getRelated()::class,
//                    'name' => $morphName,
//                    'foreign' => $relation->getForeignKeyName(),
//                    'type' => $relation->getMorphType(),
//                    'class' => $relation->getMorphClass(),
//                ],
//                'data' => $unit,
            ]);
            $index++;

//                'foreign' => $relation->getForeignKeyName(),
//                'type' => $relation->getMorphType(),
//                'class' => $relation->getMorphClass(),
//                'related' => $unit,
// TODO: Reuse code from BelongsTo
//                'data' => collect($unit)->mapWithKeys(function($identifier, $relation) {
//                    $id = $this->sample->populator->memory->get($relation->getRelated()::class, $identifier);
//
//                    return [$relation->getForeignKeyName() => $id];
//                }),
//            ]);
        }
    }

    /**
     * Mutes the attributes of the model using the defined mutators.
     *
     * @param Collection $data
     * @return Collection
     */
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

    /**
     * Inserts the model into the database.
     *
     * @param Collection $data
     * @return Collection
     */
    protected function insert(Collection $data): Collection
    {
        $id = DB::table($this->sample->table)
            ->insertGetId($data->toArray());

        // Get the ID if it's not auto incrementing
        if (!$this->sample->model->getIncrementing()) {
            $id = $data->get($this->sample->model->getKeyName());
        }

        $this->sample->populator->memory->set($this->sample->model::class, $this->name, $id);

        return $data;
    }

    /**
     * Handles the queued related models and inserts them into the database.
     *
     * @param Collection $data
     * @return Collection
     */
    protected function related(Collection $data): Collection
    {
        $id = $this->sample->populator->memory->get($this->sample->model::class, $this->name);

        foreach ($this->memory->all() as $table => $relations) {

            foreach ($relations as $name => $relation) {
//                if (!Arr::has($relation,'relation')) {
//                    dd($relations);
//                }
                if ($relation['relation'] === 'belongsToMany') {
                    DB::table($table)
                        ->insert([
                            $relation['foreign']['pivot_key'] => $id,
                            $relation['related']['pivot_key'] => $relation['related']['id'],
                        ]);
                }

                if ($relation['relation'] === 'morphMany') {
                    $sample = Sample::make($relation['related']);
                    $sample->populator = $this->sample->populator;

//                    dd($name);
                    $processor = new Processor($sample);
                    $processor->process($relation['model'], $name);

//                    $morphName = Str::beforeLast($relation->getForeignKeyName(), '_');
//                    $unit = collect($unit)
//                        ->mergeRecursive([
//                        'relations' => [
//                            $morphName => [$this->name, $relation->getMorphClass()],
//                        ]
//                    ])->toArray();
//                    DB::table($table)
//                        ->insert([
//                            Str::after($relation['foreign'], '.') => $id,
//                            Str::after($relation['type'], '.') => $relation['class'],
//                        ]);
                }
            }

        }

        return $data;
    }

    /**
     * Creates an instance of the class.
     */
    private function __construct(Sample $sample)
    {
        $this->sample = $sample;
        $this->memory = new Memory();
    }

    /**
     * Static factory to create an instance of the class.
     *
     * @param Sample $sample
     * @return static
     */
    public static function make(Sample $sample): static
    {
        return new static($sample);
    }

}
