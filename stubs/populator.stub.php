<?php
namespace DummyNamespace;

use Guava\LaravelPopulator\Populator;

class DummyClass extends Populator
{

    public function samples(): array
    {
        return [
            // Sample::make(User::class),
        ];
    }
}
