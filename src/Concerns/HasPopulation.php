<?php

namespace Guava\LaravelPopulator\Concerns;

use Guava\LaravelPopulator\Models\Population;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @mixin Model
 */
trait HasPopulation
{
    /**
     * The population entry inserted by the populator which can be used to remove inserted records
     *
     * @return MorphOne<Population>
     */
    public function population(): MorphOne
    {
        return $this->morphOne(Population::class, 'populatable');
    }
}
