<?php

use PXP\Core\Lib\Collection;
use PXP\Core\Lib\Obj;
use PXP\Core\Lib\PermamentVariable;

function dump(mixed ...$data)
{
    echo '<pre>';

    if ($data) {
        var_dump(...$data);
    }

    echo '</pre>';
}

function dd(mixed ...$data)
{
    dump(...$data);

    exit();
}

function view(string $view, array $params = [])
{
    return \PXP\Core\Lib\View::make($view, $params);
}

function c(mixed ...$items)
{
    return Collection::make($items);
}

function obj(array|object $items)
{
    return Obj::make((object) $items);
}

function request(string|array|null $key = null)
{
    if ($key === null) {
        return new \PXP\Core\Lib\Arrays($_REQUEST);
    }

    return (new \PXP\Core\Lib\Arrays($_REQUEST))->access($key);
}

function session(string|array|null $key = null)
{
    return (new \PXP\Core\Lib\Arrays($_SESSION))->access($key);
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
