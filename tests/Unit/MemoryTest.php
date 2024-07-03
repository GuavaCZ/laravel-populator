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

    public function testAllStartsEmpty(): void
    {
        $this->assertEmpty($this->storage->all());
    }

    public function testGetValue(): void
    {
        $this->assertNull($this->storage->get('foo', 'bar'));
        $this->storage->set('foo', 'bar', 'baz');
        $this->assertEquals('baz', $this->storage->get('foo', 'bar'));
    }

    public function testHasValue(): void
    {
        $this->assertFalse($this->storage->has('foo', 'bar'));
        $this->storage->set('foo', 'bar', 'baz');
        $this->assertTrue($this->storage->has('foo', 'bar'));
    }
}
