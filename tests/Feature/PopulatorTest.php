<?php

namespace Tests\Feature;

use Guava\LaravelPopulator\Bundle;
use Guava\LaravelPopulator\Populator;
use Tests\Fixtures\TestUser;
use Tests\TestCase;

class PopulatorTest extends TestCase
{
    public function test_bundles_sets_bundles()
    {
        $bundle = Bundle::make(TestUser::class);
        $this->assertEquals([$bundle], Populator::make('test')
            ->bundles([
                $bundle,
            ])->bundles);
    }

    public function test_only_handles_in_current_environment()
    {
        $bundle = $this->spy(Bundle::class);
        Populator::make('test')
            ->bundles([$bundle])
            ->call();
        $bundle->shouldHaveReceived('handle');

        $bundle = $this->spy(Bundle::class);
        Populator::make('test')
            ->environments(['local'])
            ->bundles([$bundle])
            ->call();
        $bundle->shouldNotHaveReceived('handle');
    }
}
