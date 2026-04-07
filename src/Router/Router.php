<?php

namespace PXP\Router;

use PXP\Http\Controllers\ErrorController;
use PXP\Lib\Session;

class Router
{
    /**
     * bring request uri to format "/some/uri" and strip query part
     */
    public static function path(): string
    {
        $uri = '/'.trim($_SERVER['REQUEST_URI'], "/\r\n\t ");
        return parse_url($uri, PHP_URL_PATH);
    }

    /**
     * get method either by request parameter or "real" HTTP method
     */
    public static function method(): string
    {
        return strtoupper($_REQUEST['__method'] ?? $_SERVER['REQUEST_METHOD']);
    }

    public static function route(): mixed
    {
        // find current path-method combination in route tree
        $content = self::find(Router::path(), Router::method());

        // apply middlewares
        foreach ($content->middlewares as $middleware) {
            $status = (new $middleware)->apply($content);

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
                'middlewares' => [],
            ];
        }

        // find current method for found endpoint
        $action = $endpoint->method($method);

        if ($action === null) {
            return (object) [
                'class' => ErrorController::class,
                'method' => 'methodNotSupported',
                'params' => ['route' => $path, 'method' => $method],
                'middlewares' => [],
            ];
        }

        $action['middlewares'] ??= [];
        $action['params'] = $tree->param(); // associative array of route-params

        return (object) $action;
    }
}
