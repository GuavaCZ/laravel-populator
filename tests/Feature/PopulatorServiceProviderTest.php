<?php

namespace Tests\Feature;

use Tests\TestCase;

class PopulatorServiceProviderTest extends TestCase
{
    public function test_publishes_config()
    {
        $this->artisan('vendor:publish', [
            '--provider' => 'Guava\LaravelPopulator\PopulatorServiceProvider'
        ]);

        $this->assertFileExists(config_path('populator.php'));
        $this->assertFileIsReadable(config_path('populator.php'));
//        $this->assertFileEquals(config_path('larapoke.php'), __DIR__ . '/../../config/larapoke.php');
        $this->assertTrue(unlink(config_path('populator.php')));
    }

    public function test_registers_commands()
    {
        $commands = \Artisan::all();
        $this->assertArrayHasKey('make:populator', $commands);
//        $this->assertArrayHasKey('make:sample', $commands);
    }
}