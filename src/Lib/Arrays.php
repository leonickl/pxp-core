<?php

namespace PXP\Lib;

use RuntimeException;

class Arrays
{
    public function __construct(private array &$array) {}

    public function access(string|array|null $key = null)
    {
        if (is_string($key)) {
            return $this->array[$key] ?? null;
        }

        if (is_array($key)) {
            $values = o();

            foreach ($key as $k) {
                $values->$k = $this->array[$k];
            }

            return (object) $values;
        }

        return $this;
    }

    public function array(): array
    {
        return $this->array;
    }

    public function int(string $key)
    {
        return (int) $this->access($key);
    }

    public function bool(string $key)
    {
        return $this->access($key) !== null;
    }
}
