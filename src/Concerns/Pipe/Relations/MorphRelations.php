<?php

namespace Guava\LaravelPopulator\Concerns\Pipe\Relations;

use Guava\LaravelPopulator\Exceptions\InvalidBundleException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphOneOrMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait MorphRelations
{
    /**
     * Processes the morph to relationship and sets the foreign key.
     *
     * @param  MorphTo<Model, Model>  $relation
     * @param  string[]|int[]  $value
     * @return array<string, int|string>
     *
     * @throws InvalidBundleException
     */
    protected function morphTo(MorphTo $relation, array $value): array
    {
        $id = $this->getPrimaryId(new $value[1], $value[0]);

        if (! $id) {
            $bundleName = $this->bundle->model::class;
            throw new InvalidBundleException("Item {$this->name} from Sample {$bundleName} has an invalid belongsToMany relation set for {$relation->getRelationName()} (value: {$value[0]}).");
        }

        return [$relation->getForeignKeyName() => $id, $relation->getMorphType() => $value[1]];
    }

    /**
     * Processes the morph one relationship and sets the foreign key.
     *
     * @param  MorphOne<Model>  $relation
     * @param  array<int|string>  $record
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
     * @param  MorphMany<Model>  $relation
     * @param  string[]|int[]  $items
     */
    protected function morphMany(MorphMany $relation, array $items): void
    {
        $this->morphOneOrMany($relation, $items);
    }

    /**
     * Processes the morph one or many relationship and sets the foreign key.
     *
     * @param  MorphOneOrMany<Model>  $relation
     * @param  array<int, array<int|string>>|array<int|string>  $records
     */
    protected function morphOneOrMany(MorphOneOrMany $relation, array $records): void
    {
        $index = 0;
        foreach ($records as $record) {
            $morphName = Str::beforeLast($relation->getForeignKeyName(), '_');
            $record = collect(Arr::wrap($record))->merge([
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
     * Processes the belongs to many relationship and queues the relation for creation.
     *
     * @param  MorphToMany<Model>  $relation
     * @param  string[]|int[]  $value
     *
     * @throws InvalidBundleException
     */
    protected function morphToMany(MorphToMany $relation, array $value): void
    {
        foreach ($value as $identifier) {
            $id = $this->getPrimaryId($relation->getRelated(), $identifier);

            if (! $id) {
                $bundleName = $this->bundle->model::class;
                throw new InvalidBundleException("Item {$this->name} from Sample {$bundleName} has an invalid belongsToMany relation set for {$relation->getRelationName()} (value: {$identifier}).");
            }

            $this->memory->set($relation->getTable(), $identifier, [
                'relation' => $relation::class,
                'foreign' => [
                    'pivot_key' => $relation->getForeignPivotKeyName(),
                    'morph_type' => $relation->getMorphType(),
                ],
                'related' => [
                    'pivot_key' => $relation->getRelatedPivotKeyName(),
                    'id' => $id,
                ],
            ]);
        }
    }
}
