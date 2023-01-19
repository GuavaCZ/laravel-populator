<?php

namespace Guava\LaravelPopulator\Concerns\Bundle;

use Closure;

trait HasRecords
{
    public array $records = [];

    /**
     * Adds a record to the bundle for population.
     *
     * @param string $key Key to access the record by from other records.
     * @param array|Closure $record Data to populate the record with or closure returning the data.
     * @return static
     */
    public function record(string $key, array|Closure $record): static
    {
        $this->records[$key] = is_callable($record) ? $record() : $record;

        return $this;
    }

    /**
     * Adds an array of records to the bundle for population.
     *
     * @param array|Closure $records Records to populate the bundle with or closure returning the records.
     * @return static
     */
    public function records(array|Closure $records): static
    {
        $this->records = is_callable($records) ? $records() : $records;

        return $this;
    }
}
