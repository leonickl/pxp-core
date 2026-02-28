<?php

namespace PXP\Core\Lib;

class Collection implements \ArrayAccess, \Countable, \IteratorAggregate
{
    private function __construct(private array $items) {}

    public static function make(array $items = []): self
    {
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
        return isset($this->items[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->items[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->items[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
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
        return self::make(array_filter($this->items, $callback));
    }

    public function join(string $glue = ''): string
    {
        return implode($glue, $this->items);
    }

    public function first(): mixed
    {
        return $this->items[array_keys($this->items)[0]] ?? null;
    }

    public function last(): mixed
    {
        return $this->items[array_keys($this->items)[count($this->items) - 1]] ?? null;
    }

    public function dd(mixed ...$append): void
    {
        dd($this->items, ...$append);
    }

    public function keys(): self
    {
        return self::make(array_values(array_keys($this->items)));
    }

    public function values(): self
    {
        return self::make(array_values($this->items));
    }

    public function toArray(): array
    {
        return $this->items;
    }

    public function reverse(): self
    {
        return self::make(array_reverse($this->items));
    }

    public function groupBy(callable $extractKey): array
    {
        $groups = [];

        foreach ($this as $item) {
            $key = $extractKey($item);

            if (! isset($groups[$key])) {
                $groups[$key] = [];
            }

            $groups[$key][] = $item;
        }

        return $groups;
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->items);
    }

    public function sort(callable $compare)
    {
        usort($this->items, $compare);

        return $this;
    }

    public function flatten()
    {
        $list = [];

        foreach ($this->items as $item) {
            $list = [...$list, ...$item];
        }

        return self::make($list);
    }

    public function with(mixed ...$values): self
    {
        return self::make([...$this->items, ...$values]);
    }

    public function without(mixed ...$values): self
    {
        return self::make(array_diff($this->items, $values));
    }

    public function only(mixed ...$keys): self
    {
        $list = [];

        foreach($keys as $key)
        {
            $list[] = $this->items[$key];
        }

        return self::make($list);
    }

    public function not(mixed ...$keys): self
    {
        return $this->only(...$this->keys()->without(...$keys));
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

    public function has(mixed $key): bool
    {
        return $this->keys()->includes($key);
    }
}
