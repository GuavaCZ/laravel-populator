<?php

namespace Guava\LaravelPopulator\Facades;

use Guava\LaravelPopulator\Features;
use Illuminate\Support\Facades\Facade;

/**
 * @see Features
 */
class Feature extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return Features::class;
    }
}
