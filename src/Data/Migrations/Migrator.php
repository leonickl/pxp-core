<?php

namespace PXP\Data\Migrations;
use PXP\Data\DB;

final class Migrator
{
    private function init(DB $db): void
    {
        if (! in_array('migrations', $db->tables())) {
            $db->sql('create table migrations (id integer primary key, migration string unique)');
        }
    }

    public function migrate(): void
    {
        $path = path('migrations');

        if (! file_exists($path)) {
            exit("Migrations folder does not exist.\n");
        }

        $db = DB::init();

        $this->init($db);

        $done = select('migration')
            ->from('migrations')
            ->deleted(true)
            ->execute()
            ->pluck('migration');

        foreach (scandir($path) as $file) {
            if (! str_ends_with($file, '.php')) {
                continue;
            }

            $filename = substr($file, 0, -4);

            [$stamp, $name] = explode('_', $filename, 2);

            $migration = require "$path/$file";

            if (! $migration instanceof Migration) {
                throw new RuntimeException("'$filename' does not return a migration");
            }

            echo "$filename: \t";

            if ($done->includes($filename)) {
                echo "done.";
            } else {
                echo "migrating...";
                $migration->up($db);
                $db->sql('insert into migrations (migration) values (?)', [$filename]);
            }

            echo "\n";
        }
    }
}
