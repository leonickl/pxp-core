<?php

use PXP\Core\Lib\Collection;

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

function obj(mixed ...$values)
{
    return (object) $values;
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
    $config = require __DIR__.'/config.php';

    if ($key) {
        return $config[$key] ?? $default;
    }

    return $config;
}

function path(string $path)
{
    $dir = __DIR__;

    while (!file_exists("$dir/composer.json")) {
        $parent = dirname($dir);
        if ($parent === $dir) {
            throw new \RuntimeException('Could not find project root (composer.json not found).');
        }
        $dir = $parent;
    }


    return "$dir/$path";
}