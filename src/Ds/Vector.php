<?php

namespace PXP\Ds;

use ArrayAccess;
use ArrayIterator;
use Countable;
use Exception;
use IteratorAggregate;
use Traversable;

/**
 * @template T
 * @implements ArrayAccess<int, T>
 * @implements IteratorAggregate<int, T>
 */
class Vector implements ArrayAccess, Countable, IteratorAggregate
{
    /**
     * @param list<T> $items
     */
    private function __construct(private array $items) {}

    /**
     * @param list<T> $items
     * @return Vector<T>
     */
    public static function make(array $items = []): self
    {
        // @phpstan-ignore function.alreadyNarrowedType
        if (! array_is_list($items)) {
            throw new Exception('Vector must receive a list as input');
        }

        return new self($items);
    }

    /**
     * @param T $value
     * @return Vector<T>
     */
    public static function repeat(mixed $value, int $times): self
    {
        $array = [];

        for ($i = 0; $i < $times; $i++) {
            $array[] = $value;
        }

        return self::make($array);
    }

    /**
     * @param int $offset
     */
    public function offsetExists(mixed $offset): bool
    {
        // @phpstan-ignore function.alreadyNarrowedType
        if (! is_int($offset)) {
            throw new Exception('offset must be int, '.gettype($offset).' given');
        }

        return isset($this->items[$offset]);
    }

    /**
     * @param int $offset
     */
    public function offsetGet(mixed $offset): mixed
    {
        // @phpstan-ignore function.alreadyNarrowedType
        if (! is_int($offset)) {
            throw new Exception('offset must be int, '.gettype($offset).' given');
        }

        return $this->items[$offset];
    }

    /**
     * @param int $offset
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        // @phpstan-ignore function.alreadyNarrowedType
        if (! is_int($offset)) {
            throw new Exception('offset must be int, '.gettype($offset).' given');
        }

        $this->items[$offset] = $value;
    }

    /**
     * @param int $offset
     */
    public function offsetUnset(mixed $offset): void
    {
        // @phpstan-ignore function.alreadyNarrowedType
        if (! is_int($offset)) {
            throw new Exception('offset must be int, '.gettype($offset).' given');
        }

        unset($this->items[$offset]);
    }

    public function count(): int
    {
        return count($this->items);
    }

    /**
     * @template U
     * @param callable(T, int=): U $callback
     * @return Vector<U>
     */
    public function map(callable $callback): self
    {
        $new = [];

        foreach ($this->items as $key => $value) {
            $new[$key] = $callback($value, $key);
        }

        /** @var Vector<U> */
        return self::make($new);
    }

    /**
     * @param null|(callable(T): bool) $callback
     * @return Vector<T>
     */
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

    /**
     * @return list<T>
     */
    public function toArray(): array
    {
        return $this->items;
    }

    /**
     * @return Vector<T>
     */
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

    /**
     * @param callable(T, T): int $compare
     * @return Vector<T>
     */
    public function sort(callable $compare): self
    {
        $items = $this->items;
        usort($items, $compare);

        return self::make($items);
    }

    /**
     * flattens out nested vectors
     * 
     * @return Vector<mixed>
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

    /**
     * @return Vector<T>
     */
    public function with(mixed ...$values): self
    {
        return self::make([...$this->items, ...$values]);
    }

    /**
     * @return Vector<T>
     */
    public function without(mixed ...$values): self
    {
        return self::make(
            array_values(
                array_diff($this->items, $values),
            ),
        );
    }

    /**
     * @return Vector<T>
     */
    public function only(int ...$keys): self
    {
        $list = [];

        foreach ($keys as $key) {
            $list[] = $this->items[$key];
        }

        return self::make($list);
    }

    /**
     * @return Vector<int>
     */
    public function keys(): self
    {
        /** @var list<int> */
        $keys = array_keys($this->items);

        /** @var Vector<int> */
        return self::make($keys);
    }

    /**
     * @return Vector<T>
     */
    public function not(int ...$keys): self
    {
        return $this->only(...$this->keys()->without(...$keys));
    }

    /**
     * @return Vector<T>
     */
    public function take(int $length): self
    {
        return self::make(array_slice($this->items, 0, $length));
    }

    /**
     * @param callable(T, int=): void $callback
     */
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

    /**
     * @return Vector<T>
     */
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
