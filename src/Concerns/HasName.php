<?php

namespace Guava\LaravelPopulator\Concerns;

trait HasName
{

    /**
     * Returns the name of the class.
     *
     * @param string|null $class
     * @param bool $withNamespace
     * @return string
     */
    public function getName(string $class = null, bool $withNamespace = false): string
    {
        $class = $class ?? static::class;
        $class = $withNamespace ? str($class)->replace('\\', '.') : class_basename($class);
        return str($class)
            ->whenEndsWith('Populator', fn($str) => $str->replaceLast('Populator', ''))
            ->kebab()
            ->toString();
    }

}
