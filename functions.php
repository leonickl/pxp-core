<?php

use PXP\Data\PermamentVariable;
use PXP\Data\Validate\Validator;
use PXP\Ds\Obj;
use PXP\Ds\Vector;
use PXP\Http\Response\Redirect;
use PXP\Http\Response\View;
use PXP\Lib\Arrays;
use PXP\Lib\Auth;
use PXP\Router\Route;

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

/**
 * @param  array<string, mixed>  $params
 */
function view(string $view, array $params = [], string $layout = 'app'): View
{
    return View::make($view, $params, $layout);
}

/**
 * @return Vector<mixed>
 */
function v(mixed ...$items): Vector
{
    return Vector::make(array_values($items));
}

function o(mixed ...$items): Obj
{
    return Obj::make((object) $items);
}

/**
 * @param  array<int|string, mixed>|string|null  $key
 */
function request(string|array|null $key = null): mixed
{
    return (new Arrays($_REQUEST))->access($key);
}

/**
 * @param  array<int|string, mixed>|string|null  $key
 */
function session(string|array|null $key = null): mixed
{
    return (new Arrays($_SESSION))->access($key);
}

function e(?string $string): string
{
    return htmlspecialchars($string ?? '');
}

function config(?string $key = null, mixed $default = null): mixed
{
    $config = require path('config.php');

    if ($key) {
        return $config[$key] ?? $default;
    }

    return $config;
}

function env(?string $key = null, mixed $default = null): mixed
{
    $contents = file_get_contents(path('.env'));

    if ($contents === false) {
        return $key === null ? [] : $default;
    }

    $raw = str_replace("\r\n", "\n", $contents);

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

function path(string $path = '', bool $internal = false): string
{
    $dir = __DIR__;

    while (! file_exists("$dir/vendor")) {
        $parent = dirname($dir);

        if ($parent === $dir) {
            throw new RuntimeException('Could not find project root ("vendor" dir not found).');
        }

        $dir = $parent;
    }

    if ($internal) {
        $dir .= '/vendor/leonickl/pxp-core';
    }

    return "$dir/$path";
}

/**
 * @param  array<string, mixed>|string  $name
 */
function perma(string|array $name, mixed $default = null): mixed
{
    if (is_array($name)) {
        foreach ($name as $key => $value) {
            new PermamentVariable($key)->set($value);
        }

        return null;
    }

    return new PermamentVariable($name)->get($default);
}

function auth(): bool
{
    return Auth::auth();
}

/**
 * @param  array<string, mixed>  $data
 */
function back(array $data = []): Redirect
{
    return Redirect::back($data);
}

/**
 * @param  array<string, string|int>  $params
 */
function route(string $name, array $params = []): string
{
    return Route::findByName($name)
        ->fillParams($params);
}

function validate(mixed $var, string $name = 'variable'): Validator
{
    return new Validator($var, $name);
}

/**
 * @param class-string<Exception> $class
 */
function error(string $class, mixed ...$args): never
{
    throw new $class(...$args);
}
