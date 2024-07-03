<?php

namespace Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TestRole extends Model
{
    protected $table = 'roles';

    protected $fillable = ['name'];

    /**
     * @return BelongsToMany<TestUser>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            TestUser::class,
            'role_user',
            'role_id',
            'user_id',
        );
    }
}
