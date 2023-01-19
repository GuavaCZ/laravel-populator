<?php

use App\Models\Address;
use App\Models\Comment;
use App\Models\Image;
use App\Models\Like;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use App\Populators\InitialPopulator;
use Guava\LaravelPopulator\Population\Bundle;
use Guava\LaravelPopulator\Population\Populator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
//        InitialPopulator::call();
        Populator::make('initial')
            ->bundles([
                Bundle::make(User::class)
                    ->mutate('password', fn($value) => Hash::make($value))
                    ->records([
                        'admin' => [
                            'name' => 'Administrator',
                            'email' => 'admin@guava.cz',
                            'password' => 'admin123',
                        ],
                    ])
                    ->record('webmaster', [
                        'name' => 'Webmaster',
                        'email' => 'webmaster@guava.cz',
                        'password' => 'admin123',
                        'address' => [
                            'street' => 'Example street',
                            'city' => 'Example city',
                            'zip' => '12345',
                            'state' => 'Czech Republic',
                        ]
                    ]),

                Bundle::make(Address::class),

                Bundle::make(Tag::class),
                //
                Bundle::make(Post::class),

                Bundle::make(Comment::class),

                Bundle::make(Like::class),

                Bundle::make(Image::class),

                Bundle::make(Permission::class)
                    ->default('guard_name', 'web'),

                Bundle::make(\Spatie\Permission\Models\Role::class)
                    ->default('guard_name', 'web'),
            ])
            ->call();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
