<?php

namespace Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class TestImage extends Model
{
    protected $table = 'images';

    protected $fillable = ['url'];

    public function imageable(): MorphTo
    {
        return $this->morphTo();
    }
}