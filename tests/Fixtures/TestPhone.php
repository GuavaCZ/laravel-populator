<?php

namespace Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestPhone extends Model
{
    protected $table = 'phones';

    protected $fillable = ['phone'];

    /**
     * @return BelongsTo<TestUser>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(TestUser::class,'user_id');
    }
}