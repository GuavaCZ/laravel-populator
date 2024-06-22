<?php

namespace Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class TestTag extends Model
{
    protected $table = 'tags';

    protected $fillable = ['name'];

    /**
     * @return MorphToMany<TestPost>
     */
    public function posts(): MorphToMany
    {
        return $this->morphedByMany(TestPost::class, 'taggable');
    }

    public function faux(): FakeBelongsTo
    {
        return new FakeBelongsTo(
            TestUser::query(),
            new TestUser(),
        );
    }

    public function invalid(): BelongsTo
    {
        return new BelongsTo(
            TestUser::query(),
            new TestUser(),
            'user_id',
            'id',
            'invalid',
        );
    }
}