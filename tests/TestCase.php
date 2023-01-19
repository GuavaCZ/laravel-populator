<?php

namespace Guava\LaravelPopulator\Tests;

class TestCase extends \Orchestra\Testbench\TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        // additional setup
    }

    protected function getPackageProviders($app)
    {
        return [
//            BlogPackageServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // perform environment setup
        include_once __DIR__ . '/database/migrations/2014_10_12_000000_create_users_table.php';
        include_once __DIR__ . '/database/migrations/2014_10_12_100000_create_password_resets_table.php';
        include_once __DIR__ . '/database/migrations/2019_08_19_000000_create_failed_jobs_table.php';
        include_once __DIR__ . '/database/migrations/2019_12_14_000001_create_personal_access_tokens_table.php';
        include_once __DIR__ . '/database/migrations/2023_01_16_175247_create_posts_table.php';
        include_once __DIR__ . '/database/migrations/2023_01_16_175300_create_comments_table.php';
        include_once __DIR__ . '/database/migrations/2023_01_17_122655_create_likes_table.php';
        include_once __DIR__ . '/database/migrations/2023_01_17_123933_create_tags_table.php';
        include_once __DIR__ . '/database/migrations/2023_01_17_195717_create_post_tag_table.php';
        include_once __DIR__ . '/database/migrations/2023_01_19_122407_create_addresses_table.php';
        include_once __DIR__ . '/database/migrations/2023_01_19_122939_create_images_table.php';
        include_once __DIR__ . '/database/migrations/2023_01_19_124159_create_permission_tables.php';
        include_once __DIR__ . '/database/migrations/2023_01_19_135922_create_categories_table.php';


        (new \CreateUsersTable())->up();
        (new \CreatePasswordResetsTable())->up();
        (new \CreateFailedJobsTable())->up();
        (new \CreatePersonalAccessTokensTable())->up();
        (new \CreatePostsTable())->up();
        (new \CreateCommentsTable())->up();
        (new \CreateLikesTable())->up();
        (new \CreateTagsTable())->up();
        (new \CreatePostTagTable())->up();
        (new \CreateAddressesTable())->up();
        (new \CreateImagesTable())->up();
        (new \CreatePermissionTables())->up();
        (new \CreateCategoriesTable())->up();
    }
}