<?php

namespace Tests\Fixtures;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Relation;

class FakeBelongsTo extends Relation
{

    public function addConstraints()
    {
        // TODO: Implement addConstraints() method.
    }

    public function addEagerConstraints(array $models)
    {
        // TODO: Implement addEagerConstraints() method.
    }

    public function initRelation(array $models, $relation)
    {
        // TODO: Implement initRelation() method.
    }

    public function match(array $models, Collection $results, $relation)
    {
        // TODO: Implement match() method.
    }

    public function getResults()
    {
        // TODO: Implement getResults() method.
    }
}