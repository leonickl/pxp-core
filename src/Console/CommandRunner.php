<?php

namespace PXP\Console;

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

        if (! file_exists(path('.env'))) {
            file_put_contents(path('.env'), '');
        }
    }

    public function execute(?string $command = null, string ...$args)
    {
        // internal commands
        require __DIR__.'/../../commands.php';

        // user commands
        $file = path('commands.php');

        if (file_exists($file)) {
            require $file;
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
