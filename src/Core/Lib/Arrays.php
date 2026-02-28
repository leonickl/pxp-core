<?php

namespace PXP\Core\Lib;

class Arrays
{
    public function __construct(private array $array) {}

    public function access(string|array|null $key = null)
    {
        if ($key === null) {
            return $this->array;
        }

        if (is_string($key)) {
            return $this->array[$key] ?? null;
        }

        if (is_array($key)) {
            $values = [];

            foreach ($key as $k) {
                $values[$k] = $this->access($k);
            }

            return obj($values)->toObject();
        }

        throw new \RuntimeException("invalid key '$key'");
    }

    public function array()
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
