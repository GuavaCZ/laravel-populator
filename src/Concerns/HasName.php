<?php

namespace Guava\LaravelPopulator\Concerns;

trait HasName
{
    public ?string $name;

    /**
     * Returns the name of the class.
     */
    public function getName(?string $class = null, bool $withNamespace = false): string
    {
        if ($this->name) {
            return $this->name;
        }

        $class = $class ?? static::class;
        $class = $withNamespace ? str($class)->replace('\\', '.') : class_basename($class);

        return str($class)
            ->whenEndsWith('Populator', fn ($str) => $str->replaceLast('Populator', ''))
            ->kebab()
            ->toString()
        ;
    }
}
