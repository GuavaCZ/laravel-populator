<?php

namespace Guava\LaravelPopulator\Contracts;

use Guava\LaravelPopulator\Bundle;

interface InteractsWithBundleInsert
{
    /**
     * Insert data from a Bundle into the database, returning the primary key value for the inserted model
     *
     * @param  array<string, scalar>  $data
     */
    public function insertDataFromBundle(array $data, Bundle $bundle): int | string;
}
