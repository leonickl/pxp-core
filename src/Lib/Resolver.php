<?php

namespace PXP\Lib;

use RuntimeException;

final class Resolver
{
    /**
     * @param  array<class-string, class-string>  $bindings
     */
    public function __construct(private array $bindings) {}

    /**
     * @template T of object
     *
     * @param  class-string<T>  $abstract
     * @return class-string<T>
     */
    public function resolve(string $abstract): string
    {
        if (! class_exists($abstract)) {
            throw new RuntimeException("cannot find class '$abstract'");
        }

        if (! array_key_exists($abstract, $this->bindings)) {
            throw new RuntimeException("cannot find an instance for '$abstract'");
        }

        $instance = $this->bindings[$abstract];

        if (! class_exists($instance)) {
            throw new RuntimeException("cannot find class '$instance'");
        }

        if (! is_subclass_of($instance, $abstract)) {
            throw new RuntimeException("instance '$instance' is not a subtype of '$abstract'");
        }

        return $instance;
    }

    /**
     * @template T of object
     *
     * @param  class-string<T>  $abstract
     * @return T
     */
    public function make(string $abstract, array $args): mixed
    {
        return new ($this->resolve($abstract))(...$args);
    }
}
