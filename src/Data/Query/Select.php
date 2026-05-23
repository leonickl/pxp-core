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
    ) {}

    /**
     * @param  class-string<Model>  $class
     */
    public function from(string $class): self
    {
        return new self(
            columns: $this->columns,
            class: $class,
            filters: $this->filters,
            orders: $this->orders,
            limit: $this->limit,
        );
    }

    public function where(string $column, string $operator, mixed $value, bool $or = false): self
    {
        $filter = o(column: $column, operator: $operator, value: $value, or: $or);

        return new self(
            columns: $this->columns,
            class: $this->class,
            filters: [...$this->filters, $filter],
            orders: $this->orders,
            limit: $this->limit,
        );
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
        return new self(
            columns: $this->columns,
            class: $this->class,
            filters: $this->filters,
            orders: [...$this->orders, o(by: $column, desc: $desc)],
            limit: $this->limit,
        );
    }

    public function limit(int $limit): self
    {
        return new self(
            columns: $this->columns,
            class: $this->class,
            filters: $this->filters,
            orders: $this->orders,
            limit: $limit,
        );
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
        return isset($this->limit) ? 'limit $this->limit' : '';
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

        exit($built->sql, "\n", json_encode($built->params), "\n");
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
        return $this->execute()
            ->map(fn (array $record) => (new ($this->class)(exists: true))->fill(...$record));
    }
}
