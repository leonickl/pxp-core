<?php

namespace PXP\Core\Lib;

class CommandRunner
{
    public function initDirs()
    {
        foreach (['database', 'log'] as $dir) {
            $path = __DIR__ . '/' . $dir;

            if (file_exists($path) && is_dir($path)) {
                continue;
            }

            mkdir($path);
        }
    }

    public function execute(?string $command = null, string ...$args)
    {
        if (!$command || $command === 'server') {
            shell_exec('/usr/bin/php -S localhost:8085 '.path('index.php'));
        }

        if ($command === 'migrate') {
            require __DIR__.'/migrate.php';
        }

        if ($command === 'play') {
            require __DIR__ . '/playground.php';
        }

        if ($command === 'trash') {
            $table = @$args[1];

            if(! $table) {
                die('Please enter table' . PHP_EOL);
            }

            $trashed = DB::init()->trashedOnly($table);

            foreach($trashed as $record) {
                echo json_encode(array_values($record)), PHP_EOL;
            }
        }

        if($command === 'restore') {
            $table = @$args[1];

            if(! $table) {
                die('Please enter table' . PHP_EOL);
            }

            $id = @$args[2];

            if(! $id) {
                die('Please enter an id' . PHP_EOL);
            }

            DB::init()->restore($table, (int) $id);

            echo "Restored $table with id $id.", PHP_EOL;
        }

    }
}