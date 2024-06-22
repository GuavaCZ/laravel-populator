<?php

namespace Tests\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\Fixtures\TestUser;

/**
 * @extends Factory<TestUser>
 */
class TestUserFactory extends Factory
{
    protected $model = TestUser::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
            'is_admin' => true,
        ];
    }
}
