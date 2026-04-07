<?php

namespace PXP\Data;

use Exception;
use PDO;
use PDOException;
use PXP\Lib\Log;
use PXP\Ds\Vector;

class DB
{
    private function __construct(private PDO $pdo) {}

    public static function init(): DB
    {
        try {
            return new DB(pdo: new PDO('sqlite:'.path('database/db.sqlite')));
        } catch (PDOException $e) {
            Log::log($e->getMessage());

            throw $e;
        }
    }

    public function pdo(): PDO
    {
        return $this->pdo;
    }

    /**
     * @param array<string, string> $columns
     */
    public function create(string $table, array $columns): void
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

    /**
     * @return list<array<string, mixed>>
     */
    public function all(string $table): array
    {
        $stmt = $this->pdo->prepare("select * from $table where deleted_at is null;");
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function trashedOnly(string $table): array
    {
        $stmt = $this->pdo->prepare("select * from $table where deleted_at is not null;");
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return array<string, mixed>
     */
    public function find(string $table, string $column, mixed $value): array
    {
        $stmt = $this->pdo->prepare("select * from $table where $column = ? and deleted_at is null;");
        $stmt->execute([$value]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function findAll(string $table, string $column, mixed $value): array
    {
        $stmt = $this->pdo->prepare("select * from $table where $column = ? and deleted_at is null;");
        $stmt->execute([$value]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param array<string, mixed> $record
     * @return array<string, mixed>
     */
    public function insert(string $table, array $record): array
    {
        $record['created_at'] = date('Y-m-d H:i:s');
        $record['modified_at'] = date('Y-m-d H:i:s');

        $columns = o(...$record)
            ->keys()
            ->map(fn ($x) => "`$x`")
            ->join(', ');

        $placeholders = o(...$record)
            ->keys()
            ->map(fn (string $key) => ":$key")
            ->join(', ');

        $sql = "insert into `$table` ($columns) values ($placeholders);";

        $status = $this->pdo->prepare($sql)->execute($record);

        if ($status === false) {
            throw new Exception('creating record failed');
        }

        $id = $record['id'] ?? $this->pdo->lastInsertId();

        return $this->find($table, 'id', $id);
    }

    /**
     * @param array<string, mixed> $record
     * @return array<string, mixed>
     */
    public function update(string $table, array $record): array
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

        $values = o(...$record)
            ->values()
            ->toArray();

        $status = $this->pdo->prepare($sql)->execute($values);

        if ($status === false) {
            throw new Exception('creating record failed');
        }

        return $this->find($table, 'id', $id);
    }

    /**
     * @return Vector<array<string, mixed>>
     */
    public function columnInfos(string $table): Vector
    {
        return v(...$this->pdo->query("pragma table_info(`$table`)")->fetchAll(PDO::FETCH_ASSOC));
    }

    /**
     * @return Vector<string>
     */
    public function columnNames(string $table): Vector
    {
        return $this->columnInfos($table)->map(fn (array $column) => $column['name']);
    }

    /**
     * @param array<string, string> $columns
     */
    public function addColumns(string $table, array $columns): void
    {
        $existing = $this->columnNames($table);

        foreach ($columns as $name => $type) {
            if (! $existing->includes($name)) {
                $this->pdo->exec("alter table `$table` add `$name` $type;");
            }
        }
    }

    public function delete(string $table, int $id): void
    {
        $this->pdo->prepare("update $table set deleted_at = ? where id = ?;")
            ->execute([date('Y-m-d H:i:s'), $id]);
    }

    public function restore(string $table, int $id): void
    {
        $this->pdo->prepare("update $table set deleted_at = NULL where id = ?;")
            ->execute([$id]);
    }

    /**
     * @return list<string>
     */
    public function tables(): array
    {
        return $this->pdo
            ->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name")
            ->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * @param array<string|int, mixed> $data
     * @return Vector<array<string, mixed>>
     */
    public function select(string $sql, array $data = []): Vector
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);

        return v(...$stmt->fetchAll(PDO::FETCH_ASSOC));
    }
}
