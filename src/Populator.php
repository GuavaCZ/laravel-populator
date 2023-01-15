<?php

namespace Guava\LaravelPopulator;

use Guava\LaravelPopulator\Exceptions\AbstractClassException;
use ReflectionClass;

abstract class Populator
{
    protected string $model;

    public function getModel(): string
    {
        return $this->model;
    }

    /**
     * @throws AbstractClassException
     */
    public static function call(string|array $samples): void {
        if ((new ReflectionClass(static::class))->isAbstract() ) {
            throw new AbstractClassException('Cannot call abstract Populator. You need to create and call an instance of the Populator class.');
        }

        if (is_array($samples)) {
            foreach ($samples as $sample) {
                static::call($sample);
            }
        }

        static::handle($samples);
    }

    private static function handle(string $sample): void
    {

    }

}
