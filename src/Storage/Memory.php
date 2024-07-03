<?php

namespace Guava\LaravelPopulator\Storage;

use Illuminate\Support\Arr;

/**
 * Stores data for the model in a key->value store.
 */
class Memory
{
    /**
     * @var array<class-string, array<string, mixed>>
     */
    private array $memory = [];

    /**
     * Stores the passed data in the memory.
     */
    public function set(string $model, string $key, mixed $value): void
    {
        if (! Arr::exists($this->memory, $model)) {
            Arr::set($this->memory, $model, []);
        }

        // Key can contain dots, so we cannot use dot-syntax using Arr::set().
        $this->memory[$model][$key] = $value;
    }

    /**
     * Returns the stored data for the passed model and key.
     */
    public function get(string $model, string $key): mixed
    {
        return Arr::get($this->memory, "$model.$key");
    }

    /**
     * Checks if the stored data for the passed model exists in memory.
     */
    public function has(string $model, string $key): bool
    {
        return Arr::has($this->memory, "$model.$key");
    }

    /**
     * Returns the stored data from memory.
     *
     * @return array<class-string, array<string, mixed>>
     */
    public function all(): array
    {
        return $this->memory;
    }
}
