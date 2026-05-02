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

        if ($records === []) {
            exit;
        }

        // Determine column widths
        $columns = array_keys($records[0]);
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
                echo str_pad((string) $row[$col], $colWidths[$col] + 2);
            }
            echo PHP_EOL;
        }

        exit;
    }

    $record = DB::init()->find($table, 'id', $id);

    if ($record === []) {
        exit;
    }

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

    DB::init()->delete($table, (int) $id);

    echo "trashed $id of $table\n";
});

Command::new('create-user', function (?string $username, ?string $password) {
    if (! class_exists(\App\Models\User::class)) {
        exit("User model does not exist\n");
    }

    if ($username === null) {
        exit("Please enter a username\n");
    }

    if ($password === null) {
        exit("Please enter a password\n");
    }

    $user = \App\Models\User::create(
        username: $username,
        password_hash: password_hash($password, PASSWORD_DEFAULT),
    );

    echo "created user with id $user->id\n";
});

Command::new('cache', function (?string $command) {
    if ($command === 'clear') {
        rmdir(path('cache'));
        exit("cleared cache\n");
    }

    exit("enter command, e.g. clear\n");
});

Command::new('make:model', function (?string $model, ?string $table) {
    if ($model === null) {
        exit("Please enter a model name\n");
    }

    if ($table === null) {
        exit("Please enter the table name\n");
    }

    $file = <<<PHP
    <?php

    namespace App\Models;

    use PXP\Data\Model;

    class $model extends Model
    {
        protected \$table = '$table';
    }
    PHP;

    if (! file_exists(path('src/Models'))) {
        mkdir(path('src/Models'), recursive: true);
    }

    if (file_exists(path("src/Models/$model.php"))) {
        exit("Model $model already exists\n");
    }

    file_put_contents(path("src/Models/$model.php"), $file);

    exit("Created model $model\n");
});

Command::new('make:controller', function (?string $controller) {
    if ($controller === null) {
        exit("Please enter a model name\n");
    }

    $file = <<<PHP
    <?php

    namespace App\Controllers;

    use PXP\Http\Controllers\Controller;

    class $controller extends Controller
    {
        public function index()
        {
            return view('main');
        }
    }
    PHP;

    if (! file_exists(path('src/Controllers'))) {
        mkdir(path('src/Controllers'), recursive: true);
    }

    if (file_exists(path("src/Controllers/$controller.php"))) {
        exit("Controller $controller already exists\n");
    }

    file_put_contents(path("src/Controllers/$controller.php"), $file);

    exit("Created controller $controller\n");
});
