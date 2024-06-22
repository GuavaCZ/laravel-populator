<?php

namespace Tests\Fixtures;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class TestUser extends Model implements Authenticatable
{
    use HasFactory;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'is_admin',
    ];

    protected $casts = [
        'is_admin' => 'boolean',
    ];

    public function phone(): HasOne
    {
        return $this->hasOne(TestPhone::class, 'user_id');
    }

    /**
     * @return HasMany<TestPost>
     */
    public function posts(): HasMany
    {
        return $this->hasMany(TestPost::class, 'user_id');
    }

    /**
     * @return BelongsToMany<TestUser>
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            TestRole::class,
            'role_user',
            'user_id',
            'role_id'
        );
    }

    /**
     * @return MorphOne<TestImage>
     */
    public function image(): MorphOne
    {
        return $this->morphOne(TestImage::class, 'imageable');
    }

    public function getAuthIdentifierName(): string
    {
        return 'test-user-auth-identifier-name';
    }

    public function getAuthIdentifier(): string
    {
        return 'test-user-auth-identifier';
    }

    public function getAuthPassword(): string
    {
        return 'test-user-auth-password';
    }

    public function getRememberToken(): string
    {
        return 'test-user-remember-token';
    }

    public function setRememberToken($value): void
    {
        //
    }

    public function getRememberTokenName(): string
    {
        return 'test-user-remember-token-name';
    }
}
