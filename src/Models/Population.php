<?php

namespace Guava\LaravelPopulator\Models;

use Guava\LaravelPopulator\Database\Factories\PopulationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Population extends Model
{
    use HasFactory;

    protected $guarded = [
        'populator',
        'bundle',
        'key',
    ];

    /**
     * The model that inserted by the populator
     *
     * @return MorphTo<Model, self>
     */
    public function populatable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getMorphClass(): string
    {
        return 'population';
    }

    protected static function newFactory(): PopulationFactory
    {
        return new PopulationFactory();
    }
}
