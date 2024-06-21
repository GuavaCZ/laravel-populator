<?php

namespace Tests\Unit;

use Guava\LaravelPopulator\Storage\Memory;
use PHPUnit\Framework\TestCase;

class MemoryTest extends TestCase
{
    protected Memory $storage;

    protected function setUp(): void
    {
        $this->storage = new Memory();
    }

    public function test_all_starts_empty()
    {
        $this->assertEmpty($this->storage->all());
    }

    public function test_get_value()
    {
        $this->assertNull($this->storage->get('foo', 'bar'));
        $this->storage->set('foo', 'bar', 'baz');
        $this->assertEquals('baz', $this->storage->get('foo', 'bar'));
    }

    public function test_has_value()
    {
        $this->assertFalse($this->storage->has('foo', 'bar'));
        $this->storage->set('foo', 'bar', 'baz');
        $this->assertTrue($this->storage->has('foo', 'bar'));
    }
}
