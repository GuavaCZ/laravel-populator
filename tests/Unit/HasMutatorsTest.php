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

    public function testMutateStartsEmpty(): void
    {
        $this->assertEmpty($this->target->mutators);
    }

    public function testMutate(): void
    {
        $this->target->mutate('foo', fn () => 'bar');
        $this->assertArrayHasKey('foo', $this->target->mutators);
        $this->assertIsCallable($this->target->mutators['foo']);
    }
}
