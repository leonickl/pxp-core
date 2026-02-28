<?php

namespace PXP\Ds;

use ArrayAccess;
use ArrayIterator;
use Countable;
use Exception;
use IteratorAggregate;
use Traversable;

class Vector implements ArrayAccess, Countable, IteratorAggregate
{
    private function __construct(private array $items) {}

    public static function make(array $items = []): self
    {
        if (! array_is_list($items)) {
            throw new Exception('Vector must receive a list as input');
        }

        return new self($items);
    }

    public static function repeat(mixed $value, int $times)
    {
        $array = [];

        for ($i = 0; $i < $times; $i++) {
            $array[] = $value;
        }

        return self::make($array);
    }

    public function offsetExists(mixed $offset): bool
    {
        if (! is_int($offset)) {
            throw new Exception('offset must be int, '.gettype($offset).' given');
        }

        return isset($this->items[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        if (! is_int($offset)) {
            throw new Exception('offset must be int, '.gettype($offset).' given');
        }

        return $this->items[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (! is_int($offset)) {
            throw new Exception('offset must be int, '.gettype($offset).' given');
        }

        $this->items[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        if (! is_int($offset)) {
            throw new Exception('offset must be int, '.gettype($offset).' given');
        }

        unset($this->items[$offset]);
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
        return self::make(
            array_values(
                array_filter($this->items, $callback),
            ),
        );
    }

    public function join(string $glue = ''): string
    {
        return implode($glue, $this->items);
    }

    public function first(): mixed
    {
        return $this->items[0] ?? null;
    }

    public function last(): mixed
    {
        return $this->items[count($this->items) - 1] ?? null;
    }

    public function dd(mixed ...$append): void
    {
        dd($this->items, ...$append);
    }

    public function toArray(): array
    {
        return $this->items;
    }

    public function reverse(): self
    {
        return self::make(array_reverse($this->items));
    }

    public function groupBy(callable $extractKey): Obj
    {
        $groups = Obj::make();

        foreach ($this as $item) {
            $key = $extractKey($item);

            if (! $groups->has($key)) {
                $groups[$key] = [];
            }

            $groups[$key] = [...$groups[$key], $item];
        }

        return $groups;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    public function sort(callable $compare): self
    {
        $items = $this->items;
        usort($items, $compare);

        return self::make($items);
    }

    /**
     * flattens out nested vectors
     */
    public function flatten(): self
    {
        $list = [];

        // flatten top level vectors
        foreach ($this->items as $item) {
            if ($item instanceof self) {
                $list = [...$list, ...$item->items];
            } else {
                $list[] = $item;
            }
        }

        // flatten further when a new vector is found
        foreach ($list as $item) {
            if ($item instanceof self) {
                return self::make($list)->flatten();
            }
        }

        return self::make($list);
    }

    public function with(mixed ...$values): self
    {
        return self::make([...$this->items, ...$values]);
    }

    public function without(mixed ...$values): self
    {
        return self::make(
            array_values(
                array_diff($this->items, $values),
            ),
        );
    }

    public function only(int ...$keys): self
    {
        $list = [];

        foreach ($keys as $key) {
            $list[] = $this->items[$key];
        }

        return self::make($list);
    }

    public function not(int ...$keys): self
    {
        return $this->only(...array_keys($this->items)->without(...$keys));
    }

    public function take(int $length): self
    {
        return self::make(array_slice($this->items, 0, $length));
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

    public function sample(int $n = 1): self
    {
        $indices = array_rand($this->items, $n);

        return $this->only(...is_array($indices) ? $indices : [$indices]);
    }

    public function has(int $key): bool
    {
        return $key >= 0 && $key < $this->count();
    }
}
