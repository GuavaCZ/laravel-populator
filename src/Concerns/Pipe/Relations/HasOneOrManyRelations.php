<?php

namespace Guava\LaravelPopulator\Concerns\Pipe\Relations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Support\Str;

trait HasOneOrManyRelations
{
    /**
     * Handles the hasOne relation of the processed record.
     *
     * @param  HasOne<Model>  $relation
     * @param  array<array<string|int>>  $record
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
     * @param  HasMany<Model>  $relation
     * @param  array<array<int, array<string|int>>>  $records
     */
    protected function hasMany(HasMany $relation, array $records): void
    {
        $this->hasOneOrMany($relation, $records);
    }

    /**
     * Handles the hasOneOrMany relation of the procesed record.
     *
     * @param  HasOneOrMany<Model>  $relation
     * @param  array<array<int, array<string|int>>>  $records
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
                ]),
            ]);
            $index++;
        }
    }
}
