<?php

namespace Guava\LaravelPopulator\Population;

use Closure;
use Guava\LaravelPopulator\Concerns\HasName;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class Sample
{
    use HasName;

    public Model $model;
    public string $table;

    public array $mutators = [];
    public Populator $populator;

    protected Processor $processor;

    /**
     * Parses all samples from the populators directory and attempts to insert them into the database.
     * @param Populator $populator
     * @return void
     */
    public function handle(Populator $populator): void
    {
        $this->populator = $populator;

        $path = database_path("populators/{$populator->getName()}/{$this->getName($this->model::class)}");

        collect(File::files($path))
            ->each(function (\SplFileInfo $file) {
                $this->processor->process($file);
            });
    }

//    protected function process(\SplFileInfo $file, Collection $data): void
//    {
//        $data->pipeThrough([
//            $this->relations(...),
////            $this->mutate(...),
////            $this->insert(...),
////            $this->related(...),
//        ]);
//
//
////        $data = $this->relationships($name, $data);
////        $id = $this->create($name, $data);
////        $this->createRelated($name, $data, $id);
//    }
//
//    protected function relations(Collection $data): Collection
//    {
//
//    }
//
//    /**
//     * Inserts the given data into the database table of the sample's model.
//     *
//     * Pipes the data through the mutators before inserting it.
//     *
//     * @param string $name
//     * @param Collection $data
//     * @return int
//     */
//    protected function create(string $name, Collection $data): int
//    {
//        $id = DB::table($this->table)
//            ->insertGetId(
//                $data
//                    ->except(['relationships'])
//                    ->when($this->model->usesTimestamps(),
//                        fn(Collection $collection) => $collection->merge([
//                            'created_at' => now(),
//                            'updated_at' => now(),
//                        ])
//                    )
//                    ->when(fn() => !empty($this->mutators),
//                        fn(Collection $collection) => $collection->map(function ($value, $key) {
//                            return Arr::exists($this->mutators, $key) ? $this->mutators[$key]($value) : $value;
//                        }))
////                    ->dump()
//                    ->toArray()
//            );
//
//        $this->populator->memory->set($this->model::class, $name, $id);
//
//        return $id;
//    }
//
//    public function createRelated(string $name, Collection $data, int $id): void
//    {
//        $data = $data->only('relationships');
//
//        if ($data->isEmpty()) {
//            return;
//        }
//
//        foreach ($data as $relation) {
//            if (!is_array($relation)) {
//                continue;
//            }
//
//            $relation = collect($relation);
//            $table = $relation->get('table');
//            $id = DB::table($table)
//                ->insertGetId(
//                    $relation
//                        ->except(['table'])
//                        ->mapWithKeys(function ($value) use ($id) {
//                            return [Arr::get($value, 'pivot_key') => Arr::get($value, 'id', $id)];
//                        })
//                        ->toArray()
//                );
//
//            $this->populator->memory->set($this->model::class, $name, $id);
//        }
//    }
//
//    // TODO: currently this only works for belongsTo relationships
//    // TODO: add support for hasMany and hasOne relationships
//    // TODO: add support for polymorphic relationships
//    // TODO: currently this only works for relationships that have been created before within the same populator
//    // TODO: add multiple "steps" - 1) check for existing inside database, 2) check older populators, 3) check current populator
//    // TODO: OPTIONAL 4) if nothing else, attempt to create it. For example, if an array is given, then use it to create it. similar to createMany or sometihng like that
//    protected function relationships(string $name, Collection $data): Collection
//    {
//        return $data
//            ->except(['relationships'])
//            ->when(
//                fn() => Arr::exists($data, 'relationships') && !empty($data['relationships']),
//                fn(Collection $collection) => $collection->merge(
//                    collect($data->get('relationships', []))
//                        ->mapWithKeys(function ($primaryKey, $relationshipName) {
//                            $relation = $this->model->$relationshipName($primaryKey, $relationshipName);
//
//                            if ($relation instanceof BelongsTo) {
//                                return $this->belongsToRelationship($relation, $primaryKey, $relationshipName);
//                            }
//
//                            if ($relation instanceof HasMany) {
//                                return $this->hasManyRelationship($relation, $primaryKey, $relationshipName);
//                            }
//
//                            if ($relation instanceof BelongsToMany) {
//                                return $this->belongsToManyRelationship($relation, $primaryKey, $relationshipName);
//                            }
//
//                            // TODO: throw exception
//                            return [$relationshipName => $primaryKey];
//                        })
//                        ->toArray()
//                )
//            );
//    }
//
//    protected function hasManyRelationship(HasMany $relation, string $primaryKey, string $relationshipName): array
//    {
//        throw new \Exception('Not implemented yet');
//    }
//
//    protected function belongsToManyRelationship(BelongsToMany $relation, string|array $primaryKeys, string $relationshipName): array
//    {
//        $related = $relation->getRelated()::class;
//
//        if (is_string($primaryKeys)) {
//            $primaryKeys = [$primaryKeys];
//        }
//
//        $result = [];
//        foreach ($primaryKeys as $primaryKey) {
//            if ($this->populator->memory->has($related, $primaryKey)) {
//                $value = $this->populator->memory->get($related, $primaryKey);
//            } else {
//                // TODO: Transform $primaryKey using a closure in Populator::samples()
//                // TODO: in order to support for example entering the e-mail of
//                // TODO: a user and getting the id of that user
//                $value = $primaryKey;
//            }
//            $result[] = [
//                'table' => $relation->getTable(),
//                'foreign' => [
//                    'pivot_key' => $relation->getForeignPivotKeyName(),
//                ],
//                'related' => [
//                    'pivot_key' => $relation->getRelatedPivotKeyName(),
//                    'id' => $value,
//                ],
//            ];
//        }
//
//        return [$relationshipName => $result];
//    }
//
//    protected function belongsToRelationship(BelongsTo $relation, string $primaryKey, string $relationshipName): array
//    {
//        $related = $relation->getRelated()::class;
//
//        if ($this->populator->memory->has($related, $primaryKey)) {
//            $value = $this->populator->memory->get($related, $primaryKey);
//        } else {
//            // TODO: Transform $primaryKey using a closure in Populator::samples()
//            // TODO: in order to support for example entering the e-mail of
//            // TODO: a user and getting the id of that user
//            $value = $primaryKey;
//        }
//
//        return [$relation->getForeignKeyName() => $value];
//    }

    /**
     * Mutates the specified attribute using the given callback.
     *
     * @param string $attribute Attribute to mutate.
     * @param Closure $closure Callback to run on the attribute.
     * @return $this
     */
    public function mutate(string $attribute, Closure $closure): static
    {
        $this->mutators[$attribute] = $closure;

        return $this;
    }

    /**
     * Creates an instance of the class.
     */
    private final function __construct(string $model)
    {
        $this->model = new $model;
        $this->table = $this->model->getTable();
        $this->processor = Processor::make($this);
    }


    /**
     * Static factory to create an instance of the class.
     *
     * @return static
     */
    public static function make(string $model): static
    {
        return new static($model);
    }
}
