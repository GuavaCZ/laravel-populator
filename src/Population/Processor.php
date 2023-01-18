<?php

namespace Guava\LaravelPopulator\Population;

use Guava\LaravelPopulator\Exceptions\InvalidSampleException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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
        return $data
//            ->pipe(fn($collection) => $collection
                ->mapWithKeys(function ($value, $relationName) {
                    if ($this->sample->model->isRelation($relationName)) {
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
                    } else {
                        return [$relationName => $value];
                    }

                    throw new InvalidSampleException('Relations are misconfigured in ' . $this->file->getFilename());
                })
            ;
    }

    /**
     * Processes the belongs to relationship and sets the foreign key.
     *
     * @param BelongsTo $relation
     * @param string $value
     * @return array
     * @throws InvalidSampleException
     */
    protected function belongsTo(BelongsTo $relation, string $value): array
    {
        $id = $this->getPrimaryIdFromMemory($relation->getRelated(), $value);

        if (!$id) {
            $sampleName = $this->sample->model::class;
            throw new InvalidSampleException("Item {$this->name} from Sample {$sampleName} has an invalid belongsTo relation set for {$relation->getRelationName()} (value: {$value}).");
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
            $id = $this->getPrimaryIdFromMemory($relation->getRelated(), $identifier);

            if (!$id) {
                $sampleName = $this->sample->model::class;
                throw new InvalidSampleException("Item {$this->name} from Sample {$sampleName} has an invalid belongsToMany relation set for {$relation->getRelationName()} (value: {$identifier}).");
            }

            $this->memory->set($relation->getTable(), $identifier, [
                'relation' => 'belongsToMany',
                'foreign' => [
                    'pivot_key' => $relation->getForeignPivotKeyName()
                ],
                'related' => [
                    'pivot_key' => $relation->getRelatedPivotKeyName(),
                    'id' => $id,
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
//        $id = $this->sample->populator->memory->get($value[1], $value[0]);
        $id = $this->getPrimaryIdFromMemory(new $value[1], $value[0]);

        if (!$id) {
            $sampleName = $this->sample->model::class;
            throw new InvalidSampleException("Item {$this->name} from Sample {$sampleName} has an invalid belongsToMany relation set for {$relation->getRelationName()} (value: {$identifier}).");
        }

        return [$relation->getForeignKeyName() => $id, $relation->getMorphType() => $value[1]];
    }


    /**
     * Processes the belongs to relationship and sets the foreign key.
     *
     * @param MorphMany $relation
     * @param array $items
     * @return void
     */
    protected function morphMany(MorphMany $relation, array $items): void
    {

        $index = 0;
        foreach ($items as $item) {
            $morphName = Str::beforeLast($relation->getForeignKeyName(), '_');
            $item = collect($item)->merge([
                $morphName => [$this->name, $relation->getMorphClass()],
            ])->toArray();

            $otherMorphName = Str::before($relation->getQualifiedForeignKeyName(), '.');

            $this->memory->set($relation->getRelated()->getTable(), "{$this->name}_{$otherMorphName}_{$index}", [
                'relation' => 'morphMany',
                'related' => $relation->getRelated()::class,
                'model' => $item,
            ]);
            $index++;
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

                    $processor = new Processor($sample);
                    $processor->process($relation['model'], $name);
                }
            }

        }

        return $data;
    }

    protected function getPrimaryIdFromMemory(Model $model, string $identifier): int|string|null {
        $id = $this->sample->populator->memory->get($model::class, $identifier);

        // Load from memory
        if ($id) {
            return $id;
        }

        // Load from database via primary key
        if (DB::table($model->getTable())->where($model->getKeyName(), $identifier)->first()) {
            return $identifier;
        }

        // Load from database via unique key
        if (str_contains($identifier, ':')) {
            [$key, $value] = explode(':', $identifier, 2);

            // TODO: might return null
            return DB::table($model->getTable())->where($key, $value)->first()->id;
        }

        return null;
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
