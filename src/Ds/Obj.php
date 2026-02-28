<?php

namespace PXP\Ds;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

readonly class Obj implements ArrayAccess, Countable, IteratorAggregate
{
    private function __construct(private array $items) {}

    public static function make(object $items = new stdClass): self
    {
        return new self((array) $items);
    }

    public function offsetExists(int|string $offset): bool
    {
        return isset($this->items[$offset]);
    }

    public function offsetGet(int|string $offset): mixed
    {
        return $this->items[$offset];
    }

    public function offsetSet(int|string $offset, mixed $value): void
    {
        $this->items[$offset] = $value;
    }

    public function offsetUnset(int|string $offset): void
    {
        $items = $this->items;
        unset($items[$offset]);

        return self::make($items);
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function map(callable $callback): self
    {
        $new = [];

        foreach ($this->items as $key => $value) {
            $new[$key] = $callback($value, $key);
        }

        return self::make($new);
    }

    public function filter(?callable $callback = null): self
    {
        return self::make(array_filter($this->items, $callback));
    }

    public function dd(mixed ...$append): void
    {
        dd($this->items, ...$append);
    }

    public function keys(): Vector
    {
        return Vector::make(array_values(array_keys($this->items)));
    }

    public function values(): Vector
    {
        return Vector::make(array_values($this->items));
    }

    public function toArray(): array
    {
        return $this->items;
    }

    public function toObject(): object
    {
        return (object) $this->items;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    public function sort(callable $compare)
    {
        $items = $this->items;
        usort($items, $compare);

        return self::make($items);
    }

    public function only(int|string ...$keys): self
    {
        $list = [];

        foreach ($keys as $key) {
            $list[] = $this->items[$key];
        }

        return self::make($list);
    }

    public function not(int|string ...$keys): self
    {
        return $this->only(...$this->keys()->without(...$keys));
    }

    public function each(callable $callback): void
    {
        foreach ($this as $key => $value) {
            $callback($value, $key);
        }
    }

    public function includes(mixed $value): bool
    {
        return in_array($value, $this->items);
    }

    public function has(int|string $key): bool
    {
        return $this->keys()->includes($key);
    }
}
