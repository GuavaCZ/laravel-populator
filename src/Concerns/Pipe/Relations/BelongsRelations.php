<?php

namespace Guava\LaravelPopulator\Concerns\Pipe\Relations;

use Guava\LaravelPopulator\Exceptions\InvalidBundleException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait BelongsRelations
{


    /**
     * Processes the belongs to relationship and sets the foreign key.
     *
     * @param BelongsTo $relation
     * @param string $value
     * @return array
     * @throws InvalidBundleException
     */
    protected function belongsTo(BelongsTo $relation, string $value): array
    {
        $id = $this->getPrimaryId($relation->getRelated(), $value);

        if (!$id) {
            $bundleName = $this->bundle->model::class;
            throw new InvalidBundleException("Item {$this->name} from Sample {$bundleName} has an invalid belongsTo relation set for {$relation->getRelationName()} (value: {$value}).");
        }

        return [$relation->getForeignKeyName() => $id];
    }

    /**
     * Processes the belongs to many relationship and queues the relation for creation.
     *
     * @param BelongsToMany $relation
     * @param array $value
     * @return void
     * @throws InvalidBundleException
     */
    protected function belongsToMany(BelongsToMany $relation, array $value): void
    {
        foreach ($value as $identifier) {
            $id = $this->getPrimaryId($relation->getRelated(), $identifier);

            if (!$id) {
                $bundleName = $this->bundle->model::class;
                throw new InvalidBundleException("Item {$this->name} from Sample {$bundleName} has an invalid belongsToMany relation set for {$relation->getRelationName()} (value: {$identifier}).");
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
}
