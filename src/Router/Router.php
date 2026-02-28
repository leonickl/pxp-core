<?php

namespace PXP\Router;

use PXP\Http\Controllers\ErrorController;

class Router
{
    public static function route(): mixed
    {
        // bring request uri to format "/some/uri"
        $uri = '/'.trim($_SERVER['REQUEST_URI'], "/\r\n\t ");

        // strip query part
        $path = parse_url($uri, PHP_URL_PATH);

        // get method either by request parameter or "real" HTTP method
        $method = strtoupper($_REQUEST['__method'] ?? $_SERVER['REQUEST_METHOD']);

        // find current path-method combination in route tree
        $content = self::find($path, $method);

        // apply middlewares
        foreach ($content->middlewares as $middleware) {
            $status = (new $middleware)->apply();

            if ($status !== true) {
                return $status;
            }
        }

        // execute controller method
        return (new ($content->class))->{$content->method}(...$content->params);
    }

    /**
     * @return route action for the path-method combination
     */
    private static function find(string $path, string $method): object
    {
        $routes = Route::listForTree();

        $tree = RouteTree::build($routes);

        // find current endpoint
        $endpoint = $tree->find($path);

        if ($endpoint === null || count($endpoint->method()) === 0) {
            return (object) [
                'class' => ErrorController::class,
                'method' => 'notFound',
                'params' => ['route' => $path],
            ];
        }

        // find current method for found endpoint
        $action = $endpoint->method($method);

        if ($action === null) {
            return (object) [
                'class' => ErrorController::class,
                'method' => 'methodNotSupported',
                'params' => ['route' => $path, 'method' => $method],
            ];
        }

        $action['middlewares'] ??= [];
        $action['params'] = $tree->param(); // associative array of route-params

        return (object) $action;
    }

    public static function redirect(string $uri, array $data = [])
    {
        if ($data) {
            Session::setAll($data);
        }

        header("location: $uri");
    }
}
