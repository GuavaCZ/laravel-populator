<?php

namespace Guava\LaravelPopulator;

use Guava\LaravelPopulator\Concerns\HasEnvironments;
use Guava\LaravelPopulator\Concerns\HasName;
use Guava\LaravelPopulator\Concerns\HasPipeline;
use Guava\LaravelPopulator\Storage\Memory;

/**
 * The populator is used to populate your database with the defined bundles of model records.
 *
 * @package Guava\LaravelPopulator
 */
class Populator
{
    use HasName;
    use HasEnvironments;
    use HasPipeline;

    public Memory $memory;

    public array $bundles = [];

    /**
     * Defines all bundles of the populator.
     *
     * @param array $bundles
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
     * Calls the defined samples to populate the database.
     *
     * @return void
     */
    private function handle(): void
    {
        if (!$this->checkEnvironment()) {
            return;
        }

        foreach ($this->bundles as $bundle) {
            $bundle->handle($this);
        }
    }

    /**
     * Creates an instance of the class.
     */
    private function __construct(string $name) {
        $this->name = $name;
        $this->memory = new Memory();
    }

    /**
     * Static factory to create an instance of the class.
     *
     * @param string $name
     * @return static
     */
    public static function make(string $name): static
    {
        return new static($name);
    }

}
