<?php

namespace PXP\Data;

use PXP\Exceptions\ModelNotFoundException;
use RuntimeException;
use PXP\Ds\Vector;

/**
 * @property int $id
 */
abstract class Model
{
    /**
     * @var array<string, mixed>
     */
    private array $record = [];

    final private function __construct(private bool $exists = false) {}

    public function __get(string $attr): mixed
    {
        return @$this->record[$attr];
    }

    public function __set(string $attr, mixed $value): void
    {
        $this->record[$attr] = $value;
    }

    public function fill(mixed ...$data): static
    {
        foreach ($data as $key => $value) {
            $this->record[$key] = $value;
        }

        return $this;
    }

    private static function table(): string
    {
        $object = new static;

        if (! isset($object->table)) {
            $class = static::class;
            throw new RuntimeException("please set table property for $class");
        }

        return $object->table;
    }

    /**
     * @return Vector<static>
     */
    public static function all(): Vector
    {
        $list = DB::init()->all(self::table());

        return v(...$list)->map(fn (array $record) => (new static(exists: true))->fill(...$record));
    }

    public static function find(int $id): static
    {
        return static::findBy('id', $id);
    }

    public static function findBy(string $column, mixed $value): static
    {
        $record = DB::init()->find(self::table(), $column, $value);
        
        if (! $record) {
            throw new ModelNotFoundException(static::class, $column, $value);
        }
            
        return new static(exists: true)
            ->fill(...$record);
    }

    public static function findOrNull(?int $id): ?static
    {
        if ($id === null) {
            return null;
        }

        try {
            return static::find($id);
        } catch (ModelNotFoundException) {
            return null;
        }
    }

    public static function findByOrNull(string $column, mixed $value): ?static
    {
        try {
            return static::findBy($column, $value);
        } catch (ModelNotFoundException) {
            return null;
        }
    }

    /**
     * @return Vector<static>
     */
    public static function findAllBy(string $column, mixed $value): Vector
    {
        return v(...DB::init()->findAll(self::table(), $column, $value))
            ->map(fn (array $record) => new static(exists: true)->fill(...$record));
    }

    public static function new(mixed ...$props): static
    {
        return (new static)->fill(...$props);
    }

    public static function create(mixed ...$props): static
    {
        return self::new(...$props)->save();
    }

    public function save(): static
    {
        $updated = $this->exists
            ? DB::init()->update(self::table(), $this->record)
            : DB::init()->insert(self::table(), $this->record);

        $this->fill(...$updated);

        $this->exists = true;

        return $this;
    }

    public function delete(): void
    {
        DB::init()->delete(self::table(), $this->id);
    }

    public function dd(): never
    {
        dd($this->record);
    }

    public function dump(): static
    {
        dump($this->record);

        return $this;
    }
}
