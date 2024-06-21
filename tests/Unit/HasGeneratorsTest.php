<?php

namespace Tests\Unit;

use Guava\LaravelPopulator\Concerns\Bundle\HasGenerators;
use PHPUnit\Framework\TestCase;

class HasGeneratorsTest extends TestCase
{
    protected object $target;

    protected function setUp(): void
    {
        $this->target = new class
        {
            use HasGenerators;
        };
    }

    public function test_generators_starts_empty()
    {
        $this->assertEmpty($this->target->generators);
    }

    public function test_generate()
    {
        $this->target->generate('foo', fn () => 'bar');
        $this->assertIsCallable($this->target->generators['foo']);
    }
}
