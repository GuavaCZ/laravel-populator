<?php

namespace Tests\Unit;

use Guava\LaravelPopulator\Concerns\Bundle\HasRecords;
use PHPUnit\Framework\TestCase;

class HasRecordsTest extends TestCase
{
    protected object $target;

    protected function setUp(): void
    {
        $this->target = new class
        {
            use HasRecords;
        };
    }

    public function testRecordsStartsEmpty(): void
    {
        $this->assertEmpty($this->target->records);
    }

    public function testRecord(): void
    {
        $this->target->record('foo', fn () => [
            'baz' => 'bee',
        ]);
        $this->target->record('bar', [
            'baz' => 'bee',
        ]);
        $this->assertEquals(['foo' => ['baz' => 'bee'], 'bar' => ['baz' => 'bee']], $this->target->records);
    }

    public function testRecordsCallable(): void
    {
        $this->target->records(fn () => [
            'foo' => ['baz' => 'bee'],
            'bar' => ['baz' => 'bee'],
        ]);
        $this->assertEquals(['foo' => ['baz' => 'bee'], 'bar' => ['baz' => 'bee']], $this->target->records);
    }

    public function testRecordsArray(): void
    {
        $this->target->records([
            'foo' => ['baz' => 'bee'],
            'bar' => ['baz' => 'bee'],
        ]);
        $this->assertEquals(['foo' => ['baz' => 'bee'], 'bar' => ['baz' => 'bee']], $this->target->records);
    }
}
