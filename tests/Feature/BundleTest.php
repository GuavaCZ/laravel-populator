<?php

namespace Tests\Feature;

use Guava\LaravelPopulator\Bundle;
use Guava\LaravelPopulator\Populator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Fixtures\TestPost;
use Tests\Fixtures\TestUser;
use Tests\TestCase;

class BundleTest extends TestCase
{
    use RefreshDatabase;

    public function testHandleWithStaticRecords(): void
    {
        $populator = Populator::make('manual');
        $bundle = Bundle::make(TestUser::class)
            ->records([
                'user-foo' => TestUser::factory()->raw([
                    'name' => 'Foo',
                    'email' => 'foo@example.com',
                ]),
            ])
        ;
        $bundle->handle($populator);

        $this->assertTrue(TestUser::whereEmail('foo@example.com')->exists());
    }

    public function testHandleWithFilesystemBackedRecords(): void
    {
        $populator = Populator::make('initial');
        (Bundle::make(TestUser::class))->handle($populator);
        (Bundle::make(TestPost::class))->handle($populator);

        $this->assertEquals(1, TestUser::whereEmail('foo@example.com')->withCount('posts')->sole()->posts_count);

    }

    public function testHandleWithFilesystemBackedRecordsThrowsMissingDirectoryException(): void
    {
        $this->expectExceptionMessageMatches('/^A directory for the bundle of/');
        $populator = Populator::make('initial_v2');
        $bundle = Bundle::make(TestUser::class);
        $bundle->handle($populator);
    }

    public function testHandleOnlyRunsInCorrectEnvironment(): void
    {
        $this->assertEquals(0, TestUser::count());
        $populator = Populator::make('test');
        $bundle = Bundle::make(TestUser::class)
            ->environments(['not-this-env'])
            ->record('user-foo', [
                'name' => 'Foo',
            ])
        ;
        $bundle->handle($populator);
        $this->assertEquals(0, TestUser::count());
    }

    public function testHandleInsertUsingClosure(): void
    {
        $populator = Populator::make('initial');
        $bundle = Bundle::make(TestUser::class)
            ->performInsertUsing(function (array $data, Bundle $bundle) {
                $created = $bundle->model->newInstance()->forceFill($data);
                $created->saveOrFail();

                return $created->getKey();
            })
        ;
        $bundle->handle($populator);

        $this->assertTrue(TestUser::whereEmail('foo@example.com')->exists());
    }
}
