<?php

namespace PXP\Data\Query;

use PXP\Data\DB;
use PXP\Data\Model;
use PXP\Ds\Vector;
use IllegalArgumentException;

readonly class Select
{
    public function __construct(
        private array $columns,
        private string $from = '',
        private array $joins = [],
        private array $filters = [],
        private array $orders = [],
        private ?int $limit = null,
        private ?int $offset = null,
        private bool $deleted = false,
    ) {}

    private function params(): array
    {
        $params = [];

        foreach ($this as $key => $value) {
            $params[$key] = $value;
        }

        return $params;
    }

    private function with(mixed ...$params): self
    {
        return new self(...[...$this->params(), ...$params]);
    }

    /**
     * @param  class-string<Model>|string  $class
     */
    public function from(string $from = ''): self
    {
        return $this->with(from: $from);
    }

    public function join(string $type, string $table, string $on, array $params = []): self
    {
        if (! in_array($type, ['inner', 'left', 'cross'])) {
            throw new IllegalArgumentException("Unexpected join type '$type'.");
        }

        return $this->with(joins: [
            ...$this->joins,
            o(type: $type, table: $table, on: $on, params: $params ),
        ]);
    }

    public function where(string $column, string $operator, mixed $value, bool $or = false): self
    {
        return $this->with(filters: [
            ...$this->filters,
            o(column: $column, operator: $operator, value: $value, or: $or),
        ]);
    }

    public function whereIs(string $column, mixed $value, bool $or = false): self
    {
        return $this->where($column, '=', $value, $or);
    }

    public function whereNull(string $column, bool $or = false): self
    {
        return $this->where($column, 'is null', null, $or);
    }

    public function whereNotNull(string $column, bool $or = false): self
    {
        return $this->where($column, 'is not null', null, $or);
    }

    public function order(string $column, bool $desc = false): self
    {
        return $this->with(orders: [...$this->orders, o(by: $column, desc: $desc)]);
    }

    public function limit(int $limit, int $offset = 0): self
    {
        return $this->with(limit: $limit, offset: $offset);
    }

    public function deleted(bool $deleted = true): self
    {
        return $this->with(deleted: $deleted);
    }

    private function buildColumns(): string
    {
        return implode(', ', $this->columns);
    }

    private function buildJoins(): object
    {
        $joins = [];
        $params = [];

        foreach ($this->joins as $join) {
            $joins[] = "$join->type join $join->table on $join->on";
            $params = [...$params, ...$join->params];
        }

        return o(
            sql: implode(' ', $joins),
            params: $params,
        )->toObject();
    }

    private function buildWhere(): object
    {
        $filters = [];
        $params = [];

        foreach ($this->filters as $filter) {
            $keyword = count($filters) === 0
                ? '' : ($filter->or ? 'or ' : 'and ');

            $value = $filter->value === null ? '' : ' ?';

            $filters[] = $keyword.$filter->column.' '.$filter->operator.$value;

            if ($filter->value !== null) {
                $params[] = $filter->value;
            }
        }

        return o(
            sql: implode(' ', $filters),
            params: $params,
        )->toObject();
    }

    private function buildOrder(): string
    {
        $orders = [];

        foreach ($this->orders as $order) {
            $keyword = count($orders) === 0
                ? 'order by ' : ', ';

            $direction = $order->desc ? ' desc' : ' asc';

            $orders[] = $keyword.$order->by.$direction;
        }

        return implode('', $orders);
    }

    private function buildLimit(): string
    {
        $sql = isset($this->limit) ? "limit $this->limit" : '';

        if (isset($this->offset) && $this->offset > 0) {
            $sql = "$sql offset $this->offset";
        }

        return $sql;
    }

    private function build(): object
    {
        $columns = $this->buildColumns();
        $from = class_exists($this->from) ? ($this->from)::table() : $this->from;
        $joins = $this->buildJoins();

        $where = $this->buildWhere();
        $order = $this->buildOrder();
        $limit = $this->buildLimit();

        $sql = "select $columns from $from";

        if (strlen($joins->sql) > 0) {
            $sql = "$sql $joins->sql";
        }

        if ($this->deleted) {
            if (strlen($where->sql) > 0) {
                $sql = "$sql where $where->sql";
            }
        } else {
            if (strlen($where->sql) > 0) {
                $sql = "$sql where deleted_at is null and ($where->sql)";
            } else {
                $sql = "$sql where deleted_at is null";
            }
        }

        if (strlen($order) > 0) {
            $sql = "$sql $order";
        }

        if (strlen($limit) > 0) {
            $sql = "$sql $limit";
        }

        return o(sql: $sql, params: [...$joins->params, ...$where->params])
            ->toObject();
    }

    public function dd(): never
    {
        $built = $this->build();

        dd($built->sql, "\n", json_encode($built->params), "\n");
    }

    /**
     * @param  array<string|int, mixed>  $data
     * @return Vector<array<string, mixed>>
     */
    public function execute(): Vector
    {
        $built = $this->build();

        return DB::init()
            ->select($built->sql, $built->params);
    }

    public function get(): Vector
    {
        return ($this->from)::map($this->execute());
    }
}
