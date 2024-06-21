<?php

namespace Tests\Fixtures;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestPost extends Model
{
    use HasFactory;

    protected $table = 'posts';

    protected $fillable = [
        'content',
    ];

    /**
     * @return BelongsTo<TestUser, self>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(TestUser::class, 'user_id');
    }
}
