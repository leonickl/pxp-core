<?php

namespace PXP\Data\DB;

use PDO;
use PDOException;
use Exception;

class DB
{
    private function __construct(private PDO $pdo) {}

    public static function init()
    {
        try {
            return new self(pdo: new PDO('sqlite:'.path('database/db.sqlite')));
        } catch (PDOException $e) {
            Log::log($e->getMessage());

            throw $e;
        }
    }

    public function pdo()
    {
        return $this->pdo;
    }

    public function create(string $table, array $columns)
    {
        $columns = [
            'id' => 'integer primary key',
            ...$columns,
            'created_at' => 'datetime',
            'modified_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];

        $db_columns = '';

        foreach ($columns as $name => $type) {
            $db_columns .= "$name $type";

            if (array_key_last($columns) !== $name) {
                $db_columns .= ', ';
            }
        }

        $sql = "create table if not exists $table ($db_columns);";

        $this->pdo->exec($sql);
    }

    public function all(string $table)
    {
        $stmt = $this->pdo->prepare("select * from $table where deleted_at is null;");
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function trashedOnly(string $table)
    {
        $stmt = $this->pdo->prepare("select * from $table where deleted_at is not null;");
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(string $table, string $column, mixed $value)
    {
        $stmt = $this->pdo->prepare("select * from $table where $column = ? and deleted_at is null;");
        $stmt->execute([$value]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findAll(string $table, string $column, mixed $value)
    {
        $stmt = $this->pdo->prepare("select * from $table where $column = ? and deleted_at is null;");
        $stmt->execute([$value]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insert(string $table, array $record)
    {
        $record['created_at'] = date('Y-m-d H:i:s');
        $record['modified_at'] = date('Y-m-d H:i:s');

        $columns = c(...$record)
            ->keys()
            ->map(fn ($x) => "`$x`")
            ->join(', ');

        $placeholders = c(...$record)
            ->keys()
            ->map(fn (string $key) => ":$key")
            ->join(', ');

        $sql = "insert into `$table` ($columns) values ($placeholders);";

        $status = $this->pdo->prepare($sql)->execute($record);

        if ($status === false) {
            throw new Exception('creating record failed');
        }

        $id = $this->pdo->lastInsertId();

        return $this->find($table, 'id', $id);
    }

    public function update(string $table, array $record)
    {
        $record['modified_at'] = date('Y-m-d H:i:s');

        $update = '';

        foreach ($record as $key => $value) {
            $update .= "$key = ?";

            if (array_key_last($record) !== $key) {
                $update .= ', ';
            }
        }

        $id = $record['id'];

        $sql = "update `$table` set $update where id = $id;";

        $values = c(...$record)
            ->values()
            ->toArray();

        $status = $this->pdo->prepare($sql)->execute($values);

        if ($status === false) {
            throw new Exception('creating record failed');
        }

        return $this->find($table, 'id', $id);
    }

    public function columnInfos(string $table)
    {
        return c(...$this->pdo->query("pragma table_info(`$table`)")->fetchAll(PDO::FETCH_ASSOC));
    }

    public function columnNames(string $table)
    {
        return $this->columnInfos($table)->map(fn (array $column) => $column['name']);
    }

    public function addColumns(string $table, array $columns)
    {
        $existing = $this->columnNames($table);

        foreach ($columns as $name => $type) {
            if (! $existing->includes($name)) {
                $this->pdo->exec("alter table `$table` add `$name` $type;");
            }
        }
    }

    public function delete(string $table, int $id)
    {
        $this->pdo->prepare("update $table set deleted_at = ? where id = ?;")
            ->execute([date('Y-m-d H:i:s'), $id]);
    }

    public function restore(string $table, int $id)
    {
        $this->pdo->prepare("update $table set deleted_at = NULL where id = ?;")
            ->execute([$id]);
    }

    public function tables()
    {
        return $this->pdo
            ->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name")
            ->fetchAll(PDO::FETCH_COLUMN);
    }

    public function select(string $sql, array $data = [])
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);

        return c(...$stmt->fetchAll(PDO::FETCH_ASSOC));
    }
}
