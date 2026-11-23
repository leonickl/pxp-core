<?php

use PXP\Console\Command;
use PXP\Data\DB;
use PXP\Data\Migrations\Migration;
use PXP\Data\Migrations\Migrator;

Command::new('server', function () {
    shell_exec('/usr/bin/env php -S localhost:'.config('port', 8085).' '.path('index.php'));
});

Command::new('migrate', function () {
    if (file_exists(path('migrate.php'))) {
        exit("Please migrate your old 'migrate.php' file to the new migration scheme\n");
    }

    new Migrator()->migrate();
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

    if (! $record) {
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

Command::new('make:migration', function (?string $name = null) {
    if ($name === null) {
        exit("Please enter a migration name\n");
    }

    $stamp = date('Ymd-His');
    $dir = path('migrations');

    if (! file_exists($dir)) {
        mkdir($dir, recursive: true);
    }

    $file = <<<PHP
        <?php

        use PXP\Data\DB;
        use PXP\Data\Migrations\Migration;

        return new Migration(function (DB \$db) {
            // migrate something
        });
        PHP;

    $filename = "$stamp"."_$name.php";

    if (file_exists("$dir/$filename")) {
        exit("Migration $filename already exists\n");
    }

    file_put_contents("$dir/$filename", $file);

    exit("Created migration $filename\n");
});

Command::new('make:controller', function (?string $controller) {
    if ($controller === null) {
        exit("Please enter a controller name\n");
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

Command::new('make:view', function (?string $view) {
    if ($view === null) {
        exit("Please enter a view name\n");
    }

    if (! file_exists(path('views'))) {
        mkdir(path('views'), recursive: true);
    }

    if (file_exists(path("views/$view.plate"))) {
        exit("View $view already exists\n");
    }

    file_put_contents(path("views/$view.plate"), '<h1>Welcome</h1>');

    exit("Created view $view\n");
});

Command::new('make:middleware', function (?string $middleware) {
    if ($middleware === null) {
        exit("Please enter a middleware name\n");
    }

    $file = <<<PHP
        <?php

        namespace App\Middleware;

        use PXP\Http\Middleware\Middleware;

        class $middleware extends Middleware
        {
            public function apply(): mixed
            {
                return true;
            }
        }
        PHP;    

    if (! file_exists(path('src/Middleware'))) {
        mkdir(path('src/Middleware'), recursive: true);
    }

    if (file_exists(path("src/Middleware/$middleware.php"))) {
        exit("Middleware $middleware already exists\n");
    }

    file_put_contents(path("src/Middleware/$middleware.php"), $file);

    exit("Created middleware $middleware\n");
});

Command::new('maintenance', function () {
    $token = uuidv4();

    perma(['maintenance' => $token]);

    echo "Maintenance token: $token\n";
});

Command::new('maintenance:off', function () {
    perma(['maintenance' => false]);
});

Command::new('cache:clear', function () {
    rmdir(path('cache'));
});

