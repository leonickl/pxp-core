<?php

namespace PXP\Core\Lib;

class CommandRunner
{
    public function initDirs()
    {
        foreach (['database', 'log'] as $dir) {
            $path = path($dir);

            if (file_exists($path) && is_dir($path)) {
                continue;
            }

            mkdir($path);
        }

        if(! file_exists(path('.env'))) {
            file_put_contents(path('.env'), '');
        }
    }

    public function execute(?string $command = null, string ...$args)
    {
        if (! $command || $command === 'server') {
            shell_exec('/usr/bin/php -S localhost:'.config('port', 8085).' '.path('index.php'));
        }

        if ($command === 'migrate') {
            require path('/migrate.php');
        }

        if ($command === 'play') {
            require path('/playground.php');
        }

        if ($command === 'trash') {
            $table = @$args[0];

            if (! $table) {
                exit('Please enter table'.PHP_EOL);
            }

            $trashed = DB::init()->trashedOnly($table);

            foreach ($trashed as $record) {
                echo json_encode(array_values($record)), PHP_EOL;
            }
        }

        if ($command === 'restore') {
            $table = @$args[0];

            if (! $table) {
                exit('Please enter table'.PHP_EOL);
            }

            $id = @$args[1];

            if (! $id) {
                exit('Please enter an id'.PHP_EOL);
            }

            DB::init()->restore($table, (int) $id);

            echo "Restored $table with id $id.", PHP_EOL;
        }

        if($command === 'env') {
            foreach(env() as $key => $value) {
                echo "$key: $value", PHP_EOL;
            }
        }

        if($command === 'db') {
            $table = @$args[0];

            if(! $table) {
                $tables = DB::init()->tables();

                foreach($tables as $table) {
                    echo $table, PHP_EOL;
                }

                exit;
            }

            $id = @$args[1];

            if(! $id) {
                $records = DB::init()->all($table);

                // Determine column widths
                $columns = array_keys($records[0] ?? []);
                $colWidths = [];

                foreach ($columns as $col) {
                    $colWidths[$col] = max(strlen($col), max(array_map(fn($row) => strlen((string)$row[$col]), $records)));
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
                echo str_pad($column, $maxColLen, ' ', STR_PAD_RIGHT) . " : $value\n";
            }
        }

        if($command === 'delete') {
            $table = @$args[0];

            if (! $table) {
                exit('Please enter table'.PHP_EOL);
            }

            $id = @$args[1];

            if (! $id) {
                exit('Please enter an id'.PHP_EOL);
            }

            DB::init()->delete($table, $id);

            echo "trashed $id of $table\n";
        }
    }
}
