<?php

namespace Guava\LaravelPopulator\Database\Factories;

use Guava\LaravelPopulator\Models\Population;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Population>
 */
class PopulationFactory extends Factory
{
    protected $model = Population::class;

    public function definition(): array
    {
        return [
            'populator' => $this->faker->slug(),
            'key' => $this->faker->unique()->sha1(),
            'bundle' => $this->faker->unique()->sha1(),
        ];
    }
}
