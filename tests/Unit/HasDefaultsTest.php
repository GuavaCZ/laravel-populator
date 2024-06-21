<?php

namespace Tests\Unit;

use Guava\LaravelPopulator\Concerns\Bundle\HasDefaults;
use PHPUnit\Framework\TestCase;

class HasDefaultsTest extends TestCase
{
    protected object $target;

    protected function setUp(): void
    {
        $this->target = new class
        {
            use HasDefaults;
        };
    }

    public function test_defaults_starts_empty()
    {
        $this->assertEmpty($this->target->defaults);
    }

    public function test_default()
    {
        $this->target->default('foo', 'bar');
        $this->assertArrayHasKey('foo', $this->target->defaults);
        $this->assertEquals('bar', $this->target->defaults['foo']);
    }
}
