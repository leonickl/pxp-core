<?php

namespace PXP\Router;

use PXP\Http\Controllers\ErrorController;

class Router
{
    /**
     * bring request uri to format "/some/uri" and strip query part
     */
    public static function path(): string
    {
        $uri = '/'.trim($_SERVER['REQUEST_URI'], "/\r\n\t ");

        $parsed = parse_url($uri, PHP_URL_PATH);

        return is_string($parsed) ? $parsed : '/';
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
            $result = (new $middleware)->apply();

            if ($result !== true) {
                return $result;
            }
        }

        // execute controller method
        return (new ($content->class))->{$content->method}(...$content->params);
    }

    /**
     * returns the route action for the path-method combination
     */
    private static function find(string $path, string $method): RouteAction
    {
        $tree = RouteTree::build(Route::listForTree());

        // find current endpoint
        $endpoint = $tree->find($path);

        $methods = $endpoint?->method();

        if ($endpoint === null || $methods === null || count($methods) === 0) {
            $route = new RouteAction;

            $route->class = ErrorController::class;
            $route->method = 'notFound';
            $route->params = ['route' => $path];
            $route->middlewares = [];

            return $route;
        }

        // find current method for found endpoint
        $action = $endpoint->method($method);

        if ($action === null) {
            $route = new RouteAction;

            $route->class = ErrorController::class;
            $route->method = 'methodNotSupported';
            $route->params = ['route' => $path, 'method' => $method];
            $route->middlewares = [];

            return $route;
        }

        $route = new RouteAction;

        $route->class = $action['class'];
        $route->method = $action['method'];
        $route->middlewares = $action['middlewares'];
        $route->params = $tree->param(); // associative array of route-params

        return $route;
    }
}
