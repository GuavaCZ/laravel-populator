<?php

namespace Tests\Feature\Concerns;

use Guava\LaravelPopulator\Concerns\HasName;
use Tests\TestCase;

class HasNameTest extends TestCase
{
    protected object $target;

    protected function setUp(): void
    {
        parent::setUp();

        $this->target = new class
        {
            use HasName;
        };
    }

    public function test_name_starts_uninitialized()
    {
        $this->assertFalse(isset($this->target->name));
    }

    public function test_get_name_returns_name_if_set()
    {
        $this->target->name = 'foo';
        $this->assertEquals('foo', $this->target->getName());
    }

    public function test_get_name_class_with_namespace()
    {
        $this->target->name = null;
        $this->assertEquals(
            'app.-foo', //FIXME this doesn't seem to be the desired behavior
            $this->target->getName('App\\Foo', true)
        );
    }

    public function test_get_name_class_without_namespace()
    {
        $this->target->name = null;
        $this->assertEquals(
            'foo',
            $this->target->getName('App\\Foo', false)
        );
    }

    public function test_get_name_without_class_calls_static_class_for_name()
    {
        $this->target->name = null;
        $this->assertStringStartsWith(
            'has-name-test.php',
            $this->target->getName()
        );
    }

    public function test_get_name_removes_the_string_populator_from_class()
    {
        $this->target->name = null;
        $this->assertEquals(
            'foo',
            $this->target->getName('App\\FooPopulator', false)
        );
    }
}
