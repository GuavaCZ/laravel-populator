<?php

namespace Tests\Feature;

use Guava\LaravelPopulator\Bundle;
use Guava\LaravelPopulator\Exceptions\FeatureNotEnabledException;
use Guava\LaravelPopulator\Populator;
use Guava\LaravelPopulator\Processor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Fixtures\TestPost;
use Tests\Fixtures\TestUser;
use Tests\TestCase;

class PopulatorTest extends TestCase
{
    use RefreshDatabase;

    public function testBundlesSetsBundles(): void
    {
        $bundle = Bundle::make(TestUser::class);
        $this->assertEquals([$bundle], Populator::make('test')
            ->bundles([
                $bundle,
            ])->bundles);
    }

    public function testOnlyHandlesInCurrentEnvironment(): void
    {
        $bundle = $this->spy(Bundle::class);
        Populator::make('test')
            ->bundles([$bundle])
            ->call()
        ;
        $bundle->shouldHaveReceived('handle');

        $bundle = $this->spy(Bundle::class);
        Populator::make('test')
            ->environments(['local'])
            ->bundles([$bundle])
            ->call()
        ;
        $bundle->shouldNotHaveReceived('handle');
    }

    public function testRollbackWithEmptyBundles(): void
    {

        Processor::enableTracking();
        $this->assertTrue(Processor::hasTrackingFeature());
        $populator = Populator::make('initial')
            ->bundles([
                Bundle::make(TestUser::class),
                Bundle::make(TestPost::class)
                    ->generate('id', fn () => Str::uuid()),
            ])
        ;
        $populator->call();
        $this->assertDatabaseCount(TestUser::class, 1);
        $this->assertDatabaseCount(TestPost::class, 1);
        Populator::make('initial')->rollback();
        $this->assertDatabaseCount(TestUser::class, 0);
        $this->assertDatabaseCount(TestPost::class, 0);
    }

    public function testRollbackRemovesBundleEntries(): void
    {
        Processor::enableTracking();
        $this->assertTrue(Processor::hasTrackingFeature());
        $populator = Populator::make('initial')
            ->bundles([
                Bundle::make(TestUser::class),
                Bundle::make(TestPost::class)
                    ->generate('id', fn () => Str::uuid()),
            ])
        ;
        $populator->call();
        $this->assertDatabaseCount(TestUser::class, 1);
        $this->assertDatabaseCount(TestPost::class, 1);

        $populator->rollback();

        $this->assertDatabaseCount(TestUser::class, 0);
        $this->assertDatabaseCount(TestPost::class, 0);

        $populator->call();

        $this->assertDatabaseCount(TestUser::class, 1);
        $this->assertDatabaseCount(TestPost::class, 1);

        Populator::make('initial')
            ->bundles([Bundle::make(TestPost::class)])
            ->rollback()
        ;

        $this->assertDatabaseCount(TestUser::class, 1);
        $this->assertDatabaseCount(TestPost::class, 0);

    }

    public function testRollbackThrowsIfTrackingDisabled(): void
    {
        Processor::disableTracking();
        $this->expectException(FeatureNotEnabledException::class);
        Populator::make('initial')
            ->rollback();
    }
}
