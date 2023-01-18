<?php

namespace Guava\LaravelPopulator\Population;

use Guava\LaravelPopulator\Concerns\HasEnvironments;
use Guava\LaravelPopulator\Concerns\HasName;
use Guava\LaravelPopulator\Exceptions\AbstractClassException;
use ReflectionClass;

/**
 * The populator is used to populate your database with the defined samples of model items.
 *
 * @package Guava\LaravelPopulator
 */
abstract class Populator
{
    use HasName;
    use HasEnvironments;

    public Memory $memory;

    /**
     * Define the samples you want to populate in here.
     *
     * @return Sample[]
     */
    public abstract function samples(): array;

    /**
     * Populates the database with the defined samples.
     *
     * A good way to call this method would be from a migration file.
     *
     * @throws AbstractClassException
     */
    public static function call(): void
    {
        if ((new ReflectionClass(static::class))->isAbstract()) {
            throw new AbstractClassException('Cannot call abstract Populator. You need to create and call an instance of the Populator class.');
        }

        (new static)->handle();
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

        $this->memory = new Memory;

        foreach ($this->samples() as $sample) {
            $sample->handle($this);
        }
    }

}
