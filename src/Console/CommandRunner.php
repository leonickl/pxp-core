<?php

namespace PXP\Console;

class CommandRunner
{
    public function __construct()
    {
        session_start();
    }

    public function initDirs(): void
    {
        foreach (['database', 'log', 'cache'] as $dir) {
            $path = path($dir);

            if (file_exists($path) && is_dir($path)) {
                continue;
            }

            mkdir($path, recursive: true);
        }

        if (! file_exists(path('.env'))) {
            file_put_contents(path('.env'), '');
        }
    }

    public function execute(?string $command = null, string ...$args): void
    {
        foreach (modules() as $module => $_) {
            if (file_exists($path = path('commands.php', module: $module))) {
                require $path;
            }
        }

        if ($command === null) {
            exit("please enter an command\n");
        }

        $action = Command::resolve($command);

        if ($action === null) {
            exit("command not found\n");
        }

        $action(...$args);
    }
}
