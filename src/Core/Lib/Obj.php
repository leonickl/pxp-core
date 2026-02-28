<?php

namespace PXP\Core\Lib;

class Obj
{
    private function __construct(private object $items) {}

    public static function make(object $items): self
    {
        return new self($items);
    }

    public function set(string $key, mixed $value)
    {
        $this->items->{$key} = $value;

        return $this;
    }

    public function get(string $value)
    {
        return $this->items->{$value};
    }

    public function map(callable $callback)
    {
        $new = [];

        foreach ($this->items as $key => $value) {
            $new[$key] = $callback($key, $value);
        }

        return self::make((object) $new);
    }

    public function keys()
    {
        return Collection::make(array_keys((array) $this->items));
    }

    public function values()
    {
        return Collection::make(array_values((array) $this->items));
    }

    public function toObject()
    {
        return $this->items;
    }
}
