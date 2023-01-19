<?php

namespace Guava\LaravelPopulator\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;
    /** @test */
    public function test_example()
    {
//        dump(database_path());
        $this->assertTrue(true);
    }


}
