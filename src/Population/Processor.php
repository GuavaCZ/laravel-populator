<?php

namespace Guava\LaravelPopulator\Population;

use Guava\LaravelPopulator\Exceptions\InvalidSampleException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphOneOrMany;
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
    protected Bundle $bundle;

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
            $this->defaults(...),
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
            ->mapWithKeys(function ($value, $relationName) {
                if ($this->bundle->model->isRelation($relationName)) {
                    $relation = $this->bundle->model->$relationName();

                    if ($relation instanceof MorphTo) {
                        return $this->morphTo($relation, $value);
                    }

                    if ($relation instanceof MorphOne) {
                        $this->morphOne($relation, $value);
                        return [];
                    }

                    if ($relation instanceof MorphMany) {
                        $this->morphMany($relation, $value);
                        return [];
                    }

                    if ($relation instanceof HasOne) {
                        $this->hasOne($relation, $value);
                        return [];
                    }

                    if ($relation instanceof HasMany) {
                        $this->hasMany($relation, $value);
                        return [];
                    }

                    if ($relation instanceof BelongsTo) {
                        return $this->belongsTo($relation, $value);
                    }

                    if ($relation instanceof BelongsToMany) {
                        $this->belongsToMany($relation, $value);
                        return [];
                    }

                    throw new InvalidSampleException("The relation type of {$relationName} is not supported yet.");
                } else {
                    return [$relationName => $value];
                }
            });
    }

    /**
     * Handles the hasOne relation of the processed record.
     *
     * @param HasOne $relation
     * @param array $record
     * @return void
     */
    protected function hasOne(HasOne $relation, array $record): void
    {
        $this->hasOneOrMany($relation, [
            $record,
        ]);
    }

    /**
     * Handles the hasMany relation of the processed record.
     *
     * @param HasMany $relation
     * @param array $records
     * @return void
     */
    protected function hasMany(HasMany $relation, array $records): void
    {
        $this->hasOneOrMany($relation, $records);
    }

    /**
     * Handles the hasOneOrMany relation of the procesed record.
     *
     * @param HasOneOrMany $relation
     * @param array $records
     * @return void
     */
    protected function hasOneOrMany(HasOneOrMany $relation, array $records): void
    {
        $index = 0;
        foreach ($records as $record) {
            $relationName = Str::beforeLast($relation->getForeignKeyName(), '_');

            $this->memory->set($relation->getRelated()->getTable(), "$this->name-$relationName-$index", [
                'relation' => $relation::class,
                'related' => $relation->getRelated()::class,
                'record' => array_merge($record, [
                    $relationName => $this->name,
                ])
            ]);
            $index++;
        }
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
            $bundleName = $this->bundle->model::class;
            throw new InvalidSampleException("Item {$this->name} from Sample {$bundleName} has an invalid belongsTo relation set for {$relation->getRelationName()} (value: {$value}).");
        }

        return [$relation->getForeignKeyName() => $id];
    }

    /**
     * Processes the belongs to many relationship and queues the relation for creation.
     *
     * @param BelongsToMany $relation
     * @param array $value
     * @return void
     * @throws InvalidSampleException
     */
    protected function belongsToMany(BelongsToMany $relation, array $value): void
    {
        foreach ($value as $identifier) {
            $id = $this->getPrimaryIdFromMemory($relation->getRelated(), $identifier);

            if (!$id) {
                $bundleName = $this->bundle->model::class;
                throw new InvalidSampleException("Item {$this->name} from Sample {$bundleName} has an invalid belongsToMany relation set for {$relation->getRelationName()} (value: {$identifier}).");
            }

            $this->memory->set($relation->getTable(), $identifier, [
                'relation' => $relation::class,
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
     * Processes the morph to relationship and sets the foreign key.
     *
     * @param MorphTo $relation
     * @param array $value
     * @return array
     * @throws InvalidSampleException
     */
    protected function morphTo(MorphTo $relation, array $value): array
    {
//        $id = $this->sample->populator->memory->get($value[1], $value[0]);
        $id = $this->getPrimaryIdFromMemory(new $value[1], $value[0]);

        if (!$id) {
            $bundleName = $this->bundle->model::class;
            throw new InvalidSampleException("Item {$this->name} from Sample {$bundleName} has an invalid belongsToMany relation set for {$relation->getRelationName()} (value: {$value}).");
        }

        return [$relation->getForeignKeyName() => $id, $relation->getMorphType() => $value[1]];
    }

    /**
     * Processes the morph one relationship and sets the foreign key.
     *
     * @param MorphOne $relation
     * @param array $record
     * @return void
     */
    protected function morphOne(MorphOneOrMany $relation, array $record): void
    {
        $this->morphOneOrMany($relation, [
            $record,
        ]);
    }

    /**
     * Processes the morph many relationship and sets the foreign key.
     *
     * @param MorphMany $relation
     * @param array $items
     * @return void
     */
    protected function morphMany(MorphMany $relation, array $items): void
    {
        $this->morphOneOrMany($relation, $items);
    }

    /**
     * Processes the morph one or many relationship and sets the foreign key.
     *
     * @param MorphOneOrMany $relation
     * @param array $records
     * @return void
     */
    protected function morphOneOrMany(MorphOneOrMany $relation, array $records): void
    {
        $index = 0;
        foreach ($records as $record) {
            $morphName = Str::beforeLast($relation->getForeignKeyName(), '_');
            $record = collect($record)->merge([
                $morphName => [$this->name, $relation->getMorphClass()],
            ])->toArray();

            $otherMorphName = Str::before($relation->getQualifiedForeignKeyName(), '.');

            $this->memory->set($relation->getRelated()->getTable(), "{$this->name}_{$otherMorphName}_{$index}", [
                'relation' => $relation::class,
                'related' => $relation->getRelated()::class,
                'record' => $record,
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
            ->when(fn() => !empty($this->bundle->mutators),
                fn(Collection $collection) => $collection->map(function ($value, $key) {
                    return Arr::exists($this->bundle->mutators, $key) ? $this->bundle->mutators[$key]($value) : $value;
                }));
    }

    /**
     * Adds default values for attributes that are not set.
     *
     * @param Collection $data
     * @return Collection
     */
    protected function defaults(Collection $data): Collection
    {
        return $data
            ->when($this->bundle->model->usesTimestamps(),
                fn(Collection $collection) => $collection->merge([
                    'created_at' => now(),
                    'updated_at' => now(),
                ]))
            ->when(fn() => !empty($this->bundle->defaults),
                fn(Collection $collection) => $collection->merge(
                    collect($this->bundle->defaults)
                        ->filter(fn($item, $key) => !$data->has($key))
                        ->map(function ($value) {
                            return is_callable($value) ? $value() : $value;
                        })
                ));
    }

    /**
     * Inserts the model into the database.
     *
     * @param Collection $data
     * @return Collection
     */
    protected function insert(Collection $data): Collection
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

                    $processor = new Processor($bundle);
                    $processor->process($relation['record'], $name);
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

                    $processor = new Processor($bundle);
                    $processor->process($relation['record'], $name);
                }
            }

        }

        return $data;
    }

    protected function getPrimaryIdFromMemory(Model $model, string $identifier): int|string|null
    {
        $id = $this->bundle->populator->memory->get($model::class, $identifier);

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
    private function __construct(Bundle $bundle)
    {
        $this->bundle = $bundle;
        $this->memory = new Memory();
    }

    /**
     * Static factory to create an instance of the class.
     *
     * @param Bundle $bundle
     * @return static
     */
    public static function make(Bundle $bundle): static
    {
        return new static($bundle);
    }

}
