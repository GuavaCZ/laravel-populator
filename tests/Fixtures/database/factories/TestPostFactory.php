<?php

namespace Tests\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Tests\Fixtures\TestPost;
use Tests\Fixtures\TestUser;

/**
 * @extends Factory<TestPost>
 */
class TestPostFactory extends Factory
{
    protected $model = TestPost::class;

    public function definition(): array
    {
        return [
            'user_id' => TestUser::factory(),
            'content' => $this->faker->paragraph(),
        ];
    }
}
