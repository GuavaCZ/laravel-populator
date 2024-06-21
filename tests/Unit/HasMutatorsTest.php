<?php

namespace Tests\Unit;

use Guava\LaravelPopulator\Concerns\Bundle\HasMutators;
use PHPUnit\Framework\TestCase;

class HasMutatorsTest extends TestCase
{
    protected object $target;

    protected function setUp(): void
    {
        $this->target = new class
        {
            use HasMutators;
        };
    }

    public function test_mutate_starts_empty()
    {
        $this->assertEmpty($this->target->mutators);
    }

    public function test_mutate()
    {
        $this->target->mutate('foo', fn () => 'bar');
        $this->assertArrayHasKey('foo', $this->target->mutators);
        $this->assertIsCallable($this->target->mutators['foo']);
    }
}
