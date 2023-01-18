<?php

namespace Guava\LaravelPopulator\Population;

class Item
{

    protected array $data = [];
    protected array $relations = [];

    private function __construct(array $data = [], array $relations = [])
    {
        $this->data = $data;
        $this->relations = $relations;
    }

    public static function make(array $data = [], array $relations = []): static
    {
        return new static($data, $relations);
    }

}
