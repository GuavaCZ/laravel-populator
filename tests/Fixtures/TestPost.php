<?php

namespace Tests\Fixtures;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class TestPost extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'posts';

    protected $fillable = [
        'content',
    ];

    /**
     * @return BelongsTo<TestUser, self>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(TestUser::class, 'user_id', 'id');
    }

    /**
     * @return MorphOne<TestImage>
     */
    public function image(): MorphOne
    {
        return $this->morphOne(TestImage::class, 'imageable');
    }

    /**
     * @return MorphMany<TestComment>
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(TestComment::class, 'commentable');
    }

    /**
     * @return MorphToMany<TestTag>
     */
    public function tags(): MorphToMany
    {
        return $this->morphToMany(
            TestTag::class,
            'taggable',
            'taggables',
            'taggable_id',
            'tag_id'
        );
    }
}
