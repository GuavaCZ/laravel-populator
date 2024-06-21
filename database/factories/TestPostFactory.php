<?php

namespace Guava\LaravelPopulator\Database\Factories;

use Tests\Fixtures\TestPost;
use Illuminate\Database\Eloquent\Factories\Factory;
use Tests\Fixtures\TestUser;

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