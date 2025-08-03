<?php

namespace PXP\Core\Lib;

use RuntimeException;

abstract class Model
{
    private array $record = [];

    final private function __construct(private bool $exists = false) {}

    public function __get(string $attr)
    {
        return @$this->record[$attr];
    }

    public function __set(string $attr, mixed $value)
    {
        $this->record[$attr] = $value;
    }

    public function fill(mixed ...$data)
    {
        foreach ($data as $key => $value) {
            $this->record[$key] = $value;
        }

        return $this;
    }

    private static function table()
    {
        $object = new static;

        if (! isset($object->table)) {
            $class = static::class;
            throw new RuntimeException("please set table property for $class");
        }

        return $object->table;
    }

    public static function all()
    {
        $list = \PXP\Core\Lib\DB::init()->all(self::table());

        return c(...$list)->map(fn (array $record) => (new static(true))->fill(...$record));
    }

    public static function find(int $id)
    {
        return static::findBy('id', $id);
    }

    public static function findBy(string $column, mixed $value)
    {
        $object = new static(true);

        $record = \PXP\Core\Lib\DB::init()->find(self::table(), $column, $value);

        if (! $record) {
            throw new \PXP\Core\Exceptions\ModelNotFoundException(static::class, $column, $value);
        }

        $object->fill(...$record);

        return $object;
    }

    public static function new(mixed ...$props)
    {
        return (new static)->fill(...$props);
    }

    public static function create(mixed ...$props)
    {
        return self::new(...$props)->save();
    }

    public function save()
    {
        $updated = $this->exists
            ? \PXP\Core\Lib\DB::init()->update(self::table(), $this->record)
            : \PXP\Core\Lib\DB::init()->insert(self::table(), $this->record);

        $this->fill(...$updated);

        $this->exists = true;

        return $this;
    }

    public function delete()
    {
        \PXP\Core\Lib\DB::init()->delete(self::table(), $this->id);
    }

    public function dd()
    {
        dd($this->record);
    }

    public function dump()
    {
        dump($this->record);

        return $this;
    }
}
