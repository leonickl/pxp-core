<?php

namespace PXP\Lib;

use RuntimeException;

class Arrays
{
    /**
     * @param array<string, mixed> $array
     */
    public function __construct(private array &$array) {}

    /**
     * @param string|array<string, mixed>|list<string>|null $key
     */
    public function access(string|array|null $key = null): mixed
    {
        if (is_string($key)) {
            return $this->get($key);
        }

        if (is_array($key) && array_is_list($key)) {
            $values = o();

            foreach ($key as $k) {
                $values->$k = $this->array[$k];
            }

            return (object) $values;
        }

        if (is_array($key)) {
            foreach ($key as $key => $value) {
                $this->set($key, $value);
            }

            return $this;
        }

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->array;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->array[$key] ?? $default;
    }

    public function set(string $key, mixed $value): Arrays
    {
        $this->array[$key] = $value;

        return $this;
    }

    public function unset(string $key): Arrays
    {
        unset($this->array[$key]);

        return $this;
    }

    public function take(string $key, mixed $default = null): mixed
    {
        $value = $this->get($key, $default);
        $this->unset($key);

        return $value;
    }

    public function int(string $key): int
    {
        return (int) $this->access($key);
    }

    public function string(string $key): string
    {
        return (string) $this->access($key);
    }

    public function float(string $key): float
    {
        return (float) $this->access($key);
    }

    public function bool(string $key): bool
    {
        return $this->access($key) !== null;
    }

    /**
     * @return array<mixed, mixed>
     */
    public function array(string $key): array
    {
        $data = $this->access($key);

        if ($data === null) {
            return [];
        }

        if (! is_array($data)) {
            throw new RuntimeException("'$key' is not an array");
        }

        return $data;
    }
}
