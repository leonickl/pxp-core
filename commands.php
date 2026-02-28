<?php

use PXP\Console\Command;
use PXP\Data\DB;

Command::new('server', function () {
    shell_exec('/usr/bin/env php -S localhost:'.config('port', 8085).' '.path('index.php'));
});

Command::new('migrate', function () {
    require path('/migrate.php');
});

Command::new('play', function () {
    require path('/playground.php');
});

Command::new('trash', function (?string $table = null) {
    if (! $table) {
        exit("Please enter table\n");
    }

    $trashed = DB::init()->trashedOnly($table);

    foreach ($trashed as $record) {
        echo json_encode(array_values($record)), PHP_EOL;
    }
});

Command::new('restore', function (?string $table = null, int|string|null $id = null) {
    if (! $table) {
        exit("Please enter table\n");
    }

    if (! $id) {
        exit("Please enter an id\n");
    }

    DB::init()->restore($table, (int) $id);

    echo "Restored $table with id $id.", PHP_EOL;
});

Command::new('env', function () {
    foreach (env() as $key => $value) {
        echo "$key: $value", PHP_EOL;
    }
});

Command::new('db', function (mixed ...$args) {
    $table = @$args[0];

    if (! $table) {
        $tables = DB::init()->tables();

        foreach ($tables as $table) {
            echo $table, PHP_EOL;
        }

        exit;
    }

    $id = @$args[1];

    if (! $id) {
        $records = DB::init()->all($table);

        // Determine column widths
        $columns = array_keys($records[0] ?? []);
        $colWidths = [];

        foreach ($columns as $col) {
            $colWidths[$col] = max(strlen($col), max(array_map(fn ($row) => strlen((string) $row[$col]), $records)));
        }

        // Print header
        foreach ($columns as $col) {
            echo str_pad($col, $colWidths[$col] + 2);
        }
        echo PHP_EOL;

        // Print separator
        foreach ($columns as $col) {
            echo str_repeat('-', $colWidths[$col] + 2);
        }
        echo PHP_EOL;

        // Print rows
        foreach ($records as $row) {
            foreach ($columns as $col) {
                echo str_pad($row[$col], $colWidths[$col] + 2);
            }
            echo PHP_EOL;
        }

        exit;
    }

    $record = DB::init()->find($table, 'id', $id);

    $maxColLen = max(array_map('strlen', array_keys($record)));

    foreach ($record as $column => $value) {
        echo str_pad($column, $maxColLen, ' ', STR_PAD_RIGHT)." : $value\n";
    }
});

Command::new('delete', function (?string $table = null, int|string|null $id = null) {
    if (! $table) {
        exit("Please enter table\n");
    }

    if (! $id) {
        exit("Please enter an id\n");
    }

    DB::init()->delete($table, $id);

    echo "trashed $id of $table\n";
});
