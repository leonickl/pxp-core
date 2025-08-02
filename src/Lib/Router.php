<?php

namespace PXP\Core\Lib;

class Router
{
    public static function route()
    {
        $uri = '/'.trim($_SERVER['REQUEST_URI'], "/\r\n\t ");

        $path = parse_url($uri, PHP_URL_PATH);

        $method = $_SERVER['REQUEST_METHOD'];

        @[$class, $function, $params] = self::find($path, $method);

        return (new $class)->{$function}(...$params ?? []);
    }

    private static function find(string $path, string $method)
    {
        $routes = Route::listForTree();

        $tree = RouteTree::build($routes);

        $endpoint = $tree->find($path);

        if ($endpoint === null || count($endpoint->method()) === 0) {
            return [\PXP\Core\Controllers\ErrorController::class, 'notFound', ['route' => $path]];
        }

        $action = $endpoint->method($method);

        if ($action === null) {
            return [\PXP\Core\Controllers\ErrorController::class, 'methodNotSupported', ['route' => $path, 'method' => $method]];
        }

        $action[2] = [...($action[2] ?? []), ...$tree->param()];

        return $action;
    }

    public static function redirect(string $uri, array $data = [])
    {
        if ($data) {
            Session::setAll($data);
        }

        header("location: $uri");
    }
}
