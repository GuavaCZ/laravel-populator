<?php

namespace Tests\Feature\Models;

use Guava\LaravelPopulator\Models\Population;
use Tests\Fixtures\TestUser;
use Tests\TestCase;

class PopulationTest extends TestCase
{
    public function testPopulatable(): void
    {
        $test = TestUser::factory()
            ->has(Population::factory(), 'population')
            ->create()
        ;
        $this->assertInstanceOf(Population::class, $test->population);
    }

    public function testGetMorphClass(): void
    {
        $this->assertEquals('population', (new Population)->getMorphClass());
    }
}
