<?php

namespace Tests\Feature;

use Guava\LaravelPopulator\Features;
use Guava\LaravelPopulator\Models\Population;
use Tests\Fixtures\TestPopulation;
use Tests\TestCase;

class FeaturesTest extends TestCase
{
    protected Features $features;

    protected function setUp(): void
    {
        parent::setUp();
        $this->features = new Features();
    }

    public function testHasTrackingFeature(): void
    {
        $this->features->enableTrackingFeature();
        $this->assertTrue($this->features->hasTrackingFeature());
    }

    public function testEnabled(): void
    {
        config(['populator.tracking' => true]);
        $this->assertTrue($this->features->enabled('tracking'));

        config(['populator.tracking' => false]);
        $this->assertFalse($this->features->enabled('tracking'));

    }

    public function testEnableTrackingFeature(): void
    {
        config(['populator.tracking' => false]);
        $this->assertFalse($this->features->enabled('tracking'));
        $this->features->enableTrackingFeature();
        $this->assertTrue($this->features->hasTrackingFeature());
    }

    public function testDisableTrackingFeature(): void
    {
        config(['populator.tracking' => true]);
        $this->assertTrue($this->features->enabled('tracking'));
        $this->features->disableTrackingFeature();
        $this->assertFalse($this->features->hasTrackingFeature());
    }

    public function testTracking(): void
    {
        $this->assertEquals('tracking', $this->features->tracking());
    }

    public function testHasCustomPopulationModel(): void
    {
        $this->assertFalse($this->features->hasCustomPopulationModel());

        $this->features->customPopulationModel(TestPopulation::class);
        $this->assertTrue($this->features->hasCustomPopulationModel());
    }

    public function testCustomPopulationModel(): void
    {
        $this->assertEquals(Population::class, $this->features->populationModelClass());
        $this->features->customPopulationModel(TestPopulation::class);
        $this->assertTrue($this->features->hasCustomPopulationModel());
        $this->assertInstanceOf(TestPopulation::class, $this->features->makePopulationModel());
    }
}
