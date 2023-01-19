
# Laravel Populator

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

## Usage
### Using the generator
Work in Progress

### Manual
First you need to create a migration using:
```bash
php artisan make:migration populate_initial_data

```

Inside of the migration, you have to define your populator and it's bundles:
```php
Populator::make('initial')
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

Please note that the password will not be hashed, in order to hash all passwords you can modify the Bundle like this:
```php
Bundle::make(User::class)
    ->mutate('password', fn($value) => Hash::make($value))
```
This will run call the provided callback the 'password' attribute of each record.

The package offers many more customizations, please refer to the documentation below for more information.


## Relations
Records can of course have relations with other relations. Currently supported relations are:

- one to many (`belongsTo`)
- many to many (`belongsToMany`)
- polymorphic one to many (`morphTo`) and it's inverse (`morphMany`)

### One to Many
Imagine we had a `Posts` bundle that had a one to many relation to the `author` (John Doe) created in the first example. There are three options to define the relation.

Using the record's key (the filename we chose):
```php
<?php
return [
    'name' => 'Example post',
    'slug' => 'example-post',
    'author' => 'john-doe',
];
```
Keep in mind that the above example works only for records created within the same populator, as the key is stored in temporary memory only.

Using the primary key:
```php
<?php
return [
    'name' => 'Example post',
    'slug' => 'example-post',
    'author' => 1,
];
```

Using a (preferably) unique key:
```php
<?php
return [
    'name' => 'Example post',
    'slug' => 'example-post',
    'author' => 'email:john.doe@example.tld',
];
```

It's up to you which way you prefer.

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


## Documentation



[Documentation](https://linktodocumentation)

