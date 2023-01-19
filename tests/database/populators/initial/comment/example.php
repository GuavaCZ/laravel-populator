<?php
return [
    'content' => 'Lorem ipsum dolor sit amet',

    'author' => 'webmaster',
    'post' => 'example',

//        // First check for existence inside memory, then attempt to find it in database
//        'author' => 'webmaster',

//        //BelongsTo associate: singular string > variable
//        'author' => 'webmaster',
//        //BelongsTo create: singular string > associative array
//        'author' => [
//            'name' => 'Example author',
//            'email' => 'test@example.tld',
//        ],
//        //HasMany attach: plural string > array
//        'authors' => ['webmaster', 'admin'],
//        //HasMany create: plural string > array of associative arrays
//        'authors' => [
//            [
//                'name' => 'Example author',
//                'email' => 'test@example.tld',
//            ],
//            [
//                'name' => 'Example author',
//                'email' => 'test@example.tld',
//            ],
//        ],
];
