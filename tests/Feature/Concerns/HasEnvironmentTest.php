<?php

namespace Tests\Feature\Concerns;

use Guava\LaravelPopulator\Concerns\HasEnvironments;
use Tests\TestCase;

class HasEnvironmentTest extends TestCase
{
    protected object $target;

    protected function setUp(): void
    {
        parent::setUp();

        $this->target = new class
        {
            use HasEnvironments;
        };
    }

    public function testEnvironmentsStartsEmpty(): void
    {
        $this->assertEmpty($this->target->environments);
    }

    public function testEnvironmentsSetsEnvironments(): void
    {
        $this->target->environments(['foo', 'bar']);
        $this->assertEquals(['foo', 'bar'], $this->target->environments);
    }

    public function testGetEnvironmentsReturnsEnvironments(): void
    {
        $this->target->environments(['foo', 'bar']);
        $this->assertEquals($this->target->environments, $this->target->getEnvironments());
    }

    public function testCheckEnvironmentChecksAgainstLaravelEnvironment(): void
    {
        $this->target->environments(['foo', 'bar']);
        $this->assertFalse($this->target->checkEnvironment());
        $this->target->environments(['testing']);
        $this->assertTrue($this->target->checkEnvironment());
    }
}
