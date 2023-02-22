<?php

namespace Guava\LaravelPopulator\Storage;

use Illuminate\Support\Arr;

/**
 * Stores data for the model in a key->value store.
 */
class Memory
{

    private array $memory = [];

    /**
     * Stores the passed data in the memory.
     *
     * @param string $model
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set(string $model, string $key, mixed $value): void
    {
        if (!Arr::exists($this->memory, $model)) {
            Arr::set($this->memory, $model, []);
        }

        // Key can contain dots, so we cannot use dot-syntax using Arr::set().
        $this->memory[$model][$key] = $value;
    }

    /**
     * Returns the stored data for the passed model and key.
     *
     * @param string $model
     * @param string $key
     * @return mixed
     */
    public function get(string $model, string $key): mixed
    {
        return Arr::get($this->memory, "$model.$key");
    }

    /**
     * Checks if the stored data for the passed model exists in memory.
     *
     * @param string $model
     * @param string $key
     * @return bool
     */
    public function has(string $model, string $key): bool
    {
        return Arr::has($this->memory, "$model.$key");
    }

    /**
     * Returns the stored data from memory.
     *
     * @return array
     */
    public function all(): array
    {
        return $this->memory;
    }

}
