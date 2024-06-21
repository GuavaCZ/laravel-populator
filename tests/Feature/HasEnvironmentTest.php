<?php

namespace Tests\Feature;

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

    public function test_environments_starts_empty()
    {
        $this->assertEmpty($this->target->environments);
    }

    public function test_environments_sets_environments()
    {
        $this->target->environments(['foo', 'bar']);
        $this->assertEquals(['foo', 'bar'], $this->target->environments);
    }

    public function test_get_environments_returns_environments()
    {
        $this->target->environments(['foo', 'bar']);
        $this->assertEquals($this->target->environments, $this->target->getEnvironments());
    }

    public function test_check_environment_checks_against_laravel_environment()
    {
        $this->target->environments(['foo', 'bar']);
        $this->assertFalse($this->target->checkEnvironment());
        $this->target->environments(['testing']);
        $this->assertTrue($this->target->checkEnvironment());
    }
}
