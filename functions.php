<?php

use PXP\Core\Lib\PermamentVariable;
use PXP\Core\Lib\View;
use PXP\Core\Session;
use PXP\Ds\Obj;
use PXP\Ds\Vector;
use PXP\Core\Lib\Arrays;

function dump(mixed ...$data): void
{
    echo '<pre>';

    if ($data) {
        var_dump(...$data);
    }

    echo '</pre>';
}

function dd(mixed ...$data): never
{
    dump(...$data);

    exit();
}

function view(string $view, array $params = []): View
{
    return View::make($view, $params);
}

function v(mixed ...$items): Vector
{
    return Vector::make($items);
}

function o(mixed ...$items): Obj
{
    return Obj::make((object) $items);
}

function request(string|array|null $key = null)
{
    if ($key === null) {
        return new Arrays($_REQUEST);
    }

    return (new Arrays($_REQUEST))->access($key);
}

function session(string|array|null $key = null)
{
    return Session::access($key);
}

function e(?string $string)
{
    return htmlspecialchars($string ?? '');
}

function config(?string $key = null, mixed $default = null)
{
    $config = require path('config.php');

    if ($key) {
        return $config[$key] ?? $default;
    }

    return $config;
}

function env(?string $key = null, mixed $default = null)
{
    $raw = str_replace("\r\n", "\n", file_get_contents(path('.env')));

    $env = [];

    foreach (explode("\n", $raw) as $line) {
        $line = trim($line);

        if (strlen($line) === 0 || ! str_contains($line, '=')) {
            continue;
        }

        [$name, $value] = explode('=', $line, 2);

        $env[$name] = $value;
    }

    if ($key) {
        return $env[$key] ?? $default;
    }

    return $env;
}

function path(string $path = '')
{
    $dir = __DIR__;

    while (! file_exists("$dir/vendor")) {
        $parent = dirname($dir);
        if ($parent === $dir) {
            throw new \RuntimeException('Could not find project root (composer.json not found).');
        }
        $dir = $parent;
    }

    return "$dir/$path";
}

function perma(string|array $name, mixed $default = null)
{
    if (is_array($name)) {
        foreach ($name as $key => $value) {
            new PermamentVariable($key)->set($value);
        }

        return;
    }

    if (is_string($name)) {
        return new PermamentVariable($name)->get($default);
    }
}
