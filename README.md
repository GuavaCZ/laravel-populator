
# Laravel Populator
![Packagist Version](https://img.shields.io/packagist/v/guava/laravel-populator?style=for-the-badge)


Laravel populator's goal is to provide an unified way to populate your database with predefined data, while keeping your migrations intact.

There's a lot of tutorials and opinions about how to do that. Some people manually insert data into the database inside migrations and some use seeders. In both cases, a lot of people tend to use `Model::create()`, which at first sight seems like the easiest way and it even works. However, if you change your Model's structure in the future, there's a chance your migrations stop working. For example when tinkering with the `fillable` property.

Laravel populator solves this problem while maintaining a great developer experience when dealing with your database data.


## Installation

You can install the package with composer:

```bash
  composer require guava/laravel-populator
```

## How it works

There are three major terms that Laravel Populator introduces:

**Populators**: These are basically named folders inside your `/database/populators` folder. They contain bundles.

**Bundles**: A bundle is collection of records of specific model. Bundles are part of the populator and are used to define the model of the records, default attributes or mutations.

**Records**: A record is the smallest unit and it represents a database record waiting to be populated. A record is a php file, which returns an array with `key => value` pairs describing your model / databse entry.

## Example
`2023_01_20_203807_populate_initial_data.php`:

```php

return new class extends Migration {

    public function up() {
        Populator::make('initial') // // bundles are located in /database/populators/initial/
            ->environments(['local', 'testing'])
            ->bundles([
                Bundle::make(User::class)
                    ->mutate('password', fn($value) => Hash::make($value))
                    ->records([
                        'admin' => [
                            'name' => 'Administrator',
                            'email' => 'admin@example.tld',
                            'password' => 'my-strong-password',
                        ],
                    ]),
                    
                Bundle::make(Tag::class, 'my-tags'), // records are located in /database/populators/initial/my-tags/
                    
                Bundle::make(Post::class) // records are located in /database/populators/initial/post/
                    ->generate('slug', fn(array $attributes) => Str::slug($attributes['name'])),
               
                Bundle::make(Permission::class) // records are located in /database/populators/initial/permission/
                    ->default('guard_name', 'web'),

                Bundle::make(Role::class) // records are located in /database/populators/initial/role/
                    ->default('guard_name', 'web'),
                    
            ]);
    }
    
}
```
example record `/database/populators/initial/post/example-post.php`:
```php
<?php

return [
    'name' => 'Example post',
    'content' => 'Lorem ipsum dolor sit amet',

    'author' => 'admin', // could also be ID or specific column:value, like email:admin@example.tld
    'tags' => ['Technology', 'Design', 'Off-topic'],
];
```

## Usage
### Using the generator command
Work in Progress

### Manual
First you need to create a migration using:
```bash
php artisan make:migration populate_initial_data

```

Inside of the migration, you have to define your populator and it's bundles:
```php
Populator::make('v1')
    ->bundles([
        Bundle::make(User::class),
    ])
    ->call()
```

Now you need to create the directory structure. Since we named our populator `initial` and only have one bundle for the `User` model, our structure will be:
```
/database/
    /populators/
        /initial/
            /user/
```

Now we can add as many records to the user bundle as we'd like. To do so, simply create a `php file` in the corresponding folder and name it how you want (it has to be unique across the bundle).
Let's create `john-doe.php` to create our first user, John doe:
```php
<?php
return [
    'name' => 'John Doe',
    'email' => 'john.doe@example.tld',
    'password' => 'my-strong-password',
];
```

That's it! When the migration is run, it will create all records from the populator's bundles.

Please note that the password will not be hashed, in order to hash all passwords or to learn more about all the customization options, please refer to the documentation below.

## Populators
Populators serve as a group of bundles of records that you want to populate. The reason for this kind of grouping is that during the lifetime of your application, you might want to add another batch of data to your application in mid-production. As we know, developers hate to come up with names and to avoid ending up with bundles named `users1`, `users2`, `users-new`, `yet-another-batch-of-users`, we decided to group them into populators so you only have to come up with a single name. :)

In case you end up needing to seed data in mid-production, we recommend naming your populators according to your version, such as `v1.0`, `v1.1`, `v2.0` and so on.

### Calling a populator
A populator is the entry point to everything this package offers. You can call the populator from anywhere you want, but we recommend calling them from migrations, like this:

```php
Populator::make('v1')
    ->bundles([ // Your bundles here])
    ->call()
```

This will call your populator and all it's defined bundles (more information in the Bundles section)

### Environment
Populators can be set to be executed only on specific environments. You might most likely want to seed different data for your local environment and your production environment.

You can easily do so using the `environments` method:
```php
Populator::make('v1')
    ->environments(['local'])
    ...
    ->call()
```
This populator will only be executed on the local environment.


## Bundles
Bundles are like blueprints for all your records, they define default attributes or common modifiers so you don't need to repeat them in every record. This is done by chaining additional methods described below.

Creating a bundle is as simply as this:
```php
Bundle::make(Model::class, 'optional-name'),
```
Passing a name is optional and defines the name of the directory inside the populator's directory. If omitted, the name will be auto-generated from the model's class name. For example for the model `Foo` the name will be `foo`, for the model `FooBar` it would be `foo-bar`.

### Environment
Similar to populators, you can also define specific environments for each bundle separately. To do so, chain the `environments` method on the Bundle itself:
```php
Bundle::make(User::class)
    ->environments(['production'])
```

### Mutators
You can define mutators on any of the model's attributes in order to mutate the value before it's stored in the database.

For example you might want to hash all passwords:
```php
Bundle::make(User::class)
    ->mutate('password', fn($value) => Hash::make($value))
```

### Default
You can define default attributes for all records if they are not set. This is useful if you have a lot of records with the same attributes.

For example, you might want to add `guard_name` to all permissions ([spatie/laravel-permission](https://github.com/spatie/laravel-permission)):
```php
Bundle::make(Permission::class)
    ->default('guard_name', 'web'),
```

### Generated
In case you want to have default or generated values for an attribute, you can chain a `generated()` method to your Bundle.

A common use case might be if you for example wanted to generate a slug from from another attribute:
```php
Bundle::make(Post::class)
    ->generate('slug', fn($attributes) => Str::slug($attributes['name']))
```

### Records
If you only have a small amount of records to create, it might be cumbersome to create the whole directory structure. For these cases you can create them from inside your migration using the `record` and `records` methods.

For example, to quickly create an admin account, you could do the following:
```php
...
Bundle::make(User::class)
    ->mutate('password', fn($value) => Hash::make($value))
    ->records([
        'admin' => [
            'name' => 'Administrator',
            'email' => 'admin@example.tld',
            'password' => 'admin123',
        ],
    ]);
...
```

## Relations
Records can of course have relations with other records. Currently supported relations are:

- one to one and it's inverse
- one to many and it's inverse
- many to many (`belongsToMany`)
- polymorphic one to one and it's inverse
- polymorphic one to many and it's inverse (`morphMany`)

### Referencing other records
Other records can be referenced in multiple ways, the package tries all three of them in this specific order.

#### By their identifier (key)
This works only from within the same populator (across all bundles). The key is the name of the file, so in case of the `john-doe.php` example file from earlier, the key would be `john-doe`.

In case you supplied the records to the bundle directly via the `records()` method, the key is the array key you set.

#### By their primary key
If no key was found, the package assumes the provided key is the primary key and attempts to find the record in the database. If the record exists, the primary key will be used.

#### by a (preferably unique) column
You can also reference records using any column you like. For example, to reference John Doe from other records, you could reference their e-mail using `email:john.doe@example.tld`. The package will then attempt to find the record with that e-mail address.


### One to One
Let's say we have a `User` model that has one `Address` relation. You can define the relation in the `Address` bundle like this:
```php
<?php
return [
    'street' => 'Example Street',
    'city' => 'Example City',
    'state' => 'Example State',
    'zip' => '12345',
    'user' => 'admin',
];
```
This will attempt to associate the address with the user record `admin`.

You could also reference the user their primary key:
```php
return [
    ...
    'user' => 1,
];
```

Or using a column of your choice:
```php
return [
    ...
    'user' => 'email:john.doe@example.tld',
];
```

### One to One (Inverse)
Let's say we have a `User` model that has one `Address` relation. You can create an `Address` from the `User` record:
```php
<?php
return [
    'name' => 'Webmaster',
    'email' => 'webmaster@example.tld',
    'password' => 'my-strong-password',
    'address' => [
        'street' => 'Example street',
        'city' => 'Example city',
        'zip' => '12345',
        'state' => 'Example State',
    ]
];
```
This will create a user and associate it with a newly created address record with the specified attributes.

### One to Many
Imagine we had a `Posts` bundle that had a one to many relation to the `author` (John Doe) created in the very first example. We simply use the record's key to associate it with the post.
```php
<?php
return [
    'name' => 'Example post',
    'slug' => 'example-post',
    'author' => 'john-doe',
];
```

### Many to Many
If we wanted to modify the above example and also add a `many to many` relation to `tags`, it's as simple as this:
```php
<?php
return [
    'name' => 'Example post',
    'slug' => 'example-post',
    'author' => 1,
    'tags' => ['technology', 'design', 'off-topic']
];
```
This will attach the post with the specified tags.

### Polymorphic One to Many
Now imagine we had a `Comment` model which has a polymorphic relation to the `Post` model.

Adding such a relationship is similar to a belongs to relation, but we need to pass an array with the key/primary key AND the class of the morph.
```php
<?php
return [
    'name' => 'A useful comment',
    'author' => 1,
    'post' => ['example-post', Post::class],
];
```


### Polymorphic One to Many (Inverse)
Last but not least, assuming we have `Like` model with a polymorphic relation to our Post model. Let's take another look at our `example-post` record and extend it one last time by adding an inverse `likes` relation.
```php
<?php
return [
    'name' => 'Example post',
    'slug' => 'example-post',
    'author' => 1,
    'tags' => ['technology', 'design', 'off-topic'],
    'likes' => [
        [
            'user' => 'john.doe@example.tld',
        ],
        [
            'user' => 'jennifer.doe@example.tld',
        ]
    ]
];
```
This will automatically create two Like's with the defined attributes and a relationship to the post they have been created in.

