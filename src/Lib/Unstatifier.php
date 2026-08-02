<?php

namespace PXP\Lib;

/**
 * @template T of object
 *
 * @mixin T
 */
class Unstatifier
{
    /**
     * @param  class-string<T>  $class
     */
    public function __construct(private string $class) {}

    public function __call(string $func, array $args): mixed
    {
        return ($this->class)::$func(...$args);
    }
}
