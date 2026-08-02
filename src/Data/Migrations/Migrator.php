<?php

namespace PXP\Data\Migrations;

final class Migrator
{
    public function migrate(): void
    {
        foreach (modules() as $module => $_) {
            $path = path('migrate.php', module: $module);

            if (file_exists($path)) {
                require $path;
            }
        }
    }
}
