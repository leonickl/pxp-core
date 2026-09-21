<?php

namespace PXP\Data\Query;

use PXP\Data\DB;
use PXP\Data\Model;
use PXP\Ds\Vector;

readonly class Select
{
    public function __construct(
        private array $columns,
        private string $class = '',
        private array $filters = [],
        private array $orders = [],
        private ?int $limit = null,
        private ?int $offset = null,
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
     * @param  class-string<Model>  $class
     */
    public function from(string $class): self
    {
        return $this->with(class: $class);
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

    private function buildColumns(): string
    {
        return implode(', ', $this->columns);
    }

    private function buildWhere(): object
    {
        $filters = [];
        $params = [];

        foreach ($this->filters as $filter) {
            $keyword = count($filters) === 0
                ? 'where ' : ($filter->or ? 'or ' : 'and ');

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
        $table = ($this->class)::table();

        $where = $this->buildWhere();
        $order = $this->buildOrder();
        $limit = $this->buildLimit();

        $sql = "select $columns from $table";

        if (strlen($where->sql) > 0) {
            $sql = "$sql $where->sql";
        }

        if (strlen($order) > 0) {
            $sql = "$sql $order";
        }

        if (strlen($limit) > 0) {
            $sql = "$sql $limit";
        }

        return o(sql: $sql, params: $where->params)
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
        return ($this->class)::map($this->execute());
    }
}
