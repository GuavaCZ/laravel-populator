<?php

namespace DummyNamespace;

use Guava\LaravelPopulator\Bundle;

class DummyClass extends Bundle
{
    public function setup(): void
    {
        // $this->mutate('password', fn($value) => Hash::make($value));
    }
}
