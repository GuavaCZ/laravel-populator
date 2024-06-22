<?php

namespace Guava\LaravelPopulator;

use Guava\LaravelPopulator\Models\Population;

/**
 * Feature flags for the package which get scoped per request by @see PopulatorServiceProvider
 */
class Features
{
    /**
     * Site features backing array
     *
     * @var array<string, ?scalar>
     */
    protected array $enabled = [
        'tracking' => null,
        'population_model' => null,
    ];

    /**
     * Check if a feature is currently enabled
     */
    public function enabled(string $feature): bool
    {
        return boolval(data_get($this->enabled, $feature) ?? data_get(config('populator', []), $feature, false));
    }

    /**
     * Identity for the tracking feature
     */
    public function tracking(): string
    {
        return 'tracking';
    }

    /**
     * Identity for the tracking feature
     */
    public function populationModel(): string
    {
        return 'population_model';
    }

    /**
     * Enabled state for the populated model tracking feature
     */
    public function hasTrackingFeature(): bool
    {
        return $this->enabled($this->tracking());
    }

    /**
     * Disable the populated model tracking feature
     */
    public function disableTrackingFeature(): void
    {
        $this->enabled['tracking'] = false;
    }

    /**
     * Enable the populated model tracking feature
     */
    public function enableTrackingFeature(): void
    {
        $this->enabled['tracking'] = true;
    }

    /**
     * Enabled state for the populated model tracking feature
     */
    public function hasCustomPopulationModel(): bool
    {
        return $this->populationModelClass() !== Population::class;
    }

    /**
     * Set the model used for Population
     *
     * @param  class-string  $model
     */
    public function customPopulationModel(string $model): void
    {
        $this->enabled[$this->populationModel()] = $model;
    }

    /**
     * @return class-string
     */
    public function populationModelClass(): string
    {
        return $this->enabled[$this->populationModel()] ?? Population::class;
    }

    /**
     * Instantiate Population
     */
    public function makePopulationModel(): Population
    {
        $class = $this->populationModelClass();

        return new $class;
    }
}
