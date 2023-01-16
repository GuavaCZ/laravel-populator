<?php

namespace Guava\LaravelPopulator;

use Illuminate\Support\Arr;

class Memory
{

    private array $memory = [];

    public function set(string $model, string $key, mixed $value): void
    {
        Arr::set($this->memory, "{$model}.{$key}", $value);
    }

    public function get(string $model, string $key): mixed
    {
        return Arr::get($this->memory, "$model.$key");
    }

    public function has(string $model, string $key): bool
    {
        return Arr::has($this->memory, "$model.$key");
    }

}
