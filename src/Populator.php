<?php

namespace Guava\LaravelPopulator;

use Guava\LaravelPopulator\Concerns\HasEnvironments;
use Guava\LaravelPopulator\Concerns\HasName;
use Guava\LaravelPopulator\Concerns\HasPipeline;
use Guava\LaravelPopulator\Contracts\TracksPopulatedEntries;
use Guava\LaravelPopulator\Exceptions\FeatureNotEnabledException;
use Guava\LaravelPopulator\Facades\Feature;
use Guava\LaravelPopulator\Models\Population;
use Guava\LaravelPopulator\Storage\Memory;

/**
 * The populator is used to populate your database with the defined bundles of model records.
 */
class Populator
{
    use HasEnvironments;
    use HasName;
    use HasPipeline;

    public Memory $memory;

    /**
     * @var Bundle[]
     */
    public array $bundles = [];

    /**
     * Defines all bundles of the populator.
     *
     * @param  Bundle[]  $bundles
     * @return $this
     */
    public function bundles(array $bundles): static
    {
        $this->bundles = $bundles;

        return $this;
    }

    /**
     * Populates the database with the defined samples.
     *
     * A good way to call this method would be from a migration file.
     */
    public function call(): void
    {
        $this->handle();
    }

    /**
     * Deletes any inserted records that were tracked by Population.
     *
     * In order to be eligible for tracking @return void
     *
     * @throws FeatureNotEnabledException
     *
     * @see TracksPopulatedEntries
     * and tracking must not be disabled (either by config or service provider)
     */
    public function rollback(): void
    {
        throw_unless(Feature::hasTrackingFeature(), FeatureNotEnabledException::class, 'Rollback is not allowed when tracking is disabled');

        $bundles = collect($this->bundles)
            ->map(function (Bundle $bundle) {
                return $bundle->getName();
            })
        ;

        $classes = collect($this->bundles)
            ->map(function (Bundle $bundle) {
                return $bundle->model->getMorphClass();
            })
        ;

        Population::where('populator', '=', $this->getName())
            ->where(function ($query) use ($bundles, $classes) {
                return $query
                    ->when($classes->isNotEmpty(), fn ($query) => $query->whereIn('populatable_type', $classes))
                    ->when($bundles->isNotEmpty(), fn ($query) => $query->whereIn('bundle', $bundles))
                ;
            })
            ->orderBy('id', 'desc')
            ->lazy()
            ->each(function (Population $record) {
                $record->populatable()->delete();
                $record->delete();
            })
        ;
    }

    /**
     * Calls the defined samples to populate the database.
     */
    private function handle(): void
    {
        if (! $this->checkEnvironment()) {
            return;
        }

        foreach ($this->bundles as $bundle) {
            $bundle->handle($this);
        }
    }

    /**
     * Creates an instance of the class.
     */
    final private function __construct(string $name)
    {
        $this->name = $name;
        $this->memory = new Memory();
    }

    /**
     * Static factory to create an instance of the class.
     */
    public static function make(string $name): static
    {
        return new static($name);
    }
}
