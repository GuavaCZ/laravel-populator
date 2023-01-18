<?php
namespace DummyNamespace;

use Guava\LaravelPopulator\Population\Sample;

class DummyClass extends Sample
{

    public function setup(): void {
        // $this->mutate('password', fn($value) => Hash::make($value));
    }

}
