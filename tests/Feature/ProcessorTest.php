<?php

namespace Tests\Feature;

use Guava\LaravelPopulator\Bundle;
use Guava\LaravelPopulator\Exceptions\InvalidBundleException;
use Guava\LaravelPopulator\Populator;
use Guava\LaravelPopulator\Processor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Fixtures\TestComment;
use Tests\Fixtures\TestImage;
use Tests\Fixtures\TestPhone;
use Tests\Fixtures\TestPost;
use Tests\Fixtures\TestRole;
use Tests\Fixtures\TestTag;
use Tests\Fixtures\TestUser;
use Tests\TestCase;

class ProcessorTest extends TestCase
{
    use RefreshDatabase;

    public function test_resolves_primary_ids()
    {
        Populator::make('test')
            ->bundles([
                Bundle::make(TestUser::class)
                    ->record('user-test', [
                        'id' => 1,
                        'email' => 'foo@example.com',
                        'name' => 'foo',
                        'password' => 'password',
                        'phone' => [
                            'phone' => '5555555555',
                        ]
                    ]),
                Bundle::make(TestPost::class)
                    ->generate('id', fn() => Str::uuid())
                    ->records([
                        'post-one'=> [
                            'user' => 1,
                            'content' => 'test one',
                        ],
                        'post-two'=> [
                            'user' => 'email:foo@example.com',
                            'content' => 'test two',
                        ],
                    ]),
            ])
            ->call();
        $this->assertEquals(2, TestUser::sole()->withCount('posts')->sole()->posts_count);
    }

    public function test_has_one_relation()
    {
        $this->assertEquals(0, TestPhone::count());
        Populator::make('test')
            ->bundles([
                Bundle::make(TestUser::class)
                ->record('user-test', [
                    'email' => 'foo@example.com',
                    'name' => 'foo',
                    'password' => 'password',
                    'phone' => [
                        'phone' => '5555555555',
                    ]
                ])
            ])
            ->call();

        $this->assertEquals(1, TestPhone::count());
    }

    public function test_has_one_relation_throws_on_invalid_relationship()
    {
        $this->expectException(InvalidBundleException::class);
        $this->expectExceptionMessageMatches('/has an invalid .*? relation set/');
        Populator::make('test')
            ->bundles([
                Bundle::make(TestPhone::class)
                    ->record('user-test', [
                        'user' => 'invalid',
                        'phone' => '5555555555',
                    ])
            ])
            ->call();

    }

    public function test_has_many_relation()
    {
        $this->assertEquals(0, TestPost::count());
        Populator::make('test')
            ->bundles([
                Bundle::make(TestUser::class)
                    ->record('user-test', [
                        'email' => 'foo@example.com',
                        'name' => 'foo',
                        'password' => 'password',
                        'posts' => [
                            [
                                'id' => '1a4d5b58-8ee4-4adc-9628-98dcc5691b63',
                                'content' => 'foobar',
                            ],
                        ]
                    ])
            ])
            ->call();

        $this->assertEquals(1, TestPost::count());
    }

    public function test_belongs_to_many_relation()
    {
        Populator::make('test')
            ->bundles([
                Bundle::make(TestRole::class)
                ->record('role-users', [
                    'name' => 'users',
                ]),
                Bundle::make(TestUser::class)
                    ->record('user-test', [
                        'email' => 'foo@example.com',
                        'name' => 'foo',
                        'password' => 'password',
                        'roles' => [
                            'role-users',
                        ]
                    ])
            ])
            ->call();

        $this->assertEquals(1,
            TestUser::whereEmail('foo@example.com')
                ->withCount('roles')->sole()->roles_count,
        );
    }

    public function test_belongs_to_many_relation_throws_on_invalid_relationship(){

        $this->expectException(InvalidBundleException::class);
        $this->expectExceptionMessageMatches('/has an invalid .*? relation set/');

        Populator::make('test')
            ->bundles([
                Bundle::make(TestRole::class)
                    ->record('role-users', [
                        'name' => 'users',
                    ]),
                Bundle::make(TestUser::class)
                    ->record('user-test', [
                        'email' => 'foo@example.com',
                        'name' => 'foo',
                        'password' => 'password',
                        'roles' => [
                            'role-users-invalid',
                        ]
                    ])
            ])
            ->call();

    }

    public function test_morphs_one_relation()
    {
        $this->assertEquals(0, TestImage::count());
        Populator::make('test')
            ->bundles([
                Bundle::make(TestUser::class)
                    ->record('user-test', [
                        'email' => 'foo@example.com',
                        'name' => 'foo',
                        'password' => 'password',
                        'image' => [
                            'url' => 'localhost/image.png',
                        ]
                    ])
            ])
            ->call();

        $this->assertEquals(1, TestImage::count());
    }

    public function test_morphs_one_relation_throws_on_invalid_relationship(){
        $this->expectException(InvalidBundleException::class);
        $this->expectExceptionMessageMatches('/has an invalid .*? relation set/');
        Populator::make('test')
            ->bundles([
                Bundle::make(TestImage::class)
                    ->record('invalid-test', [
                        'url' => 'localhost/image.png',
                        'imageable' => ['invalid-test', TestUser::class],
                    ])
            ])
            ->call();
    }

    public function test_morphs_many_relation()
    {
        Populator::make('test')
            ->bundles([
                Bundle::make(TestUser::class)
                    ->record('user-test', [
                        'email' => 'foo@example.com',
                        'name' => 'foo',
                        'password' => 'password',
                    ]),
                Bundle::make(TestPost::class)
                    ->generate('id', fn() => Str::uuid())
                    ->record('post-test', [
                        'user' => 'user-test',
                        'content' => 'foobar',
                        'comments' => [
                            ['body' => 'comment body']
                        ]
                    ])
            ])
            ->call();

        $this->assertEquals(1, TestPost::withCount('comments')->sole()->comments_count);

    }

    public function test_morphs_many_relation_throws_on_invalid_relationship() {
        $this->expectException(InvalidBundleException::class);
        $this->expectExceptionMessageMatches('/has an invalid .*? relation set/');
        Populator::make('test')
            ->bundles([
                Bundle::make(TestComment::class)
                    ->record('invalid-test', [
                        'body' => 'comment body',
                        'commentable' => ['invalid-test', TestPost::class],
                    ])
            ])
            ->call();
    }

    public function test_morphs_to_many_relation_throws_on_invalid_relationship() {
        $this->expectException(InvalidBundleException::class);
        $this->expectExceptionMessageMatches('/has an invalid .*? relation set/');

        Populator::make('test')
            ->bundles([
                Bundle::make(TestUser::class)
                    ->record('user-test', [
                        'email' => 'foo@example.com',
                        'name' => 'foo',
                        'password' => 'password',
                    ]),
                Bundle::make(TestPost::class)
                    ->generate('id', fn() => Str::uuid())
                    ->record('post-test', [
                        'user' => 'user-test',
                        'content' => 'foobar',
                        'tags' => ['invalid-tag'],
                    ])
            ])
            ->call();
    }

    public function test_morphs_to_many_relation()
    {
        Populator::make('test')
            ->bundles([
                Bundle::make(TestTag::class)
                ->record('tag-test', [
                    'name' => 'test',
                ]),
                Bundle::make(TestUser::class)
                    ->record('user-test', [
                        'email' => 'foo@example.com',
                        'name' => 'foo',
                        'password' => 'password',
                    ]),
                Bundle::make(TestPost::class)
                    ->generate('id', fn() => Str::uuid())
                    ->record('post-test', [
                        'user' => 'user-test',
                        'content' => 'foobar',
                        'tags' => ['tag-test'],
                    ])
            ])
            ->call();

        $this->assertEquals(1,
            TestPost::withCount('tags')->sole()->tags_count
        );

    }

    public function test_throws_for_invalid_relationship()
    {
        $this->expectException(InvalidBundleException::class);
        $this->expectExceptionMessage('The relation type of faux is not supported yet');
        Populator::make('test')
            ->bundles([
                Bundle::make(TestTag::class)
                    ->record('tag-test', [
                        'name' => 'test',
                        'faux' => '',
                    ]),
            ])->call();
    }
}