<?php

namespace Guava\LaravelPopulator\Population;

use Guava\LaravelPopulator\Concerns\HasEnvironments;
use Guava\LaravelPopulator\Concerns\HasName;

/**
 * The populator is used to populate your database with the defined samples of model items.
 *
 * @package Guava\LaravelPopulator
 */
class Populator
{
    use HasName;
    use HasEnvironments;

    public Memory $memory;

    public array $bundles = [];

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

        $this->memory = new Memory();

        foreach ($this->bundles as $sample) {
            $sample->handle($this);
        }
    }

    private function __construct(string $name) {
        $this->name = $name;
    }

    public static function make(string $name): static
    {
        return new static($name);
    }

}
