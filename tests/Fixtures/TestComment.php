<?php

namespace Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class TestComment extends Model
{
    protected $table = 'comments';

    protected $fillable = ['body'];

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }
}
