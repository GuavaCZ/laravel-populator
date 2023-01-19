<?php

use App\Models\Post;

return [
    'user' => 'webmaster',
    'likeable' => ['slug:example-post', Post::class],
];
