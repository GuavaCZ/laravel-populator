<?php

namespace Tests\Fixtures;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    /**
     * @return HasMany<TestPost>
     */
    public function posts(): HasMany
    {
        return $this->hasMany(TestPost::class, 'user_id');
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
