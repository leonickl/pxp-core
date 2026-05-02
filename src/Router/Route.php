<?php

namespace PXP\Router;

use Exception;

class Route
{
    /**
     * store all defined routes
     *
     * @var list<Route>
     */
    private static array $routes = [];

    /**
     * @param  null|array{class-string<\PXP\Http\Controllers\Controller>, string}  $action
     * @param  list<class-string<\PXP\Http\Middleware\Middleware>>  $middlewares
     */
    private function __construct(
        private readonly string $route,
        private readonly string $method,

        private ?string $name = null,
        private ?array $action = null,

        private array $middlewares = [],
    ) {}

    private static function register(string $route, string $method): self
    {
        $new = new self($route, $method);
        self::$routes[] = $new;

        return $new;
    }

    public static function get(string $route): self
    {
        return self::register($route, 'GET');
    }

    public static function post(string $route): self
    {
        return self::register($route, 'POST');
    }

    public function name(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function do(string $controller, string $method): self
    {
        /** @var class-string<\PXP\Http\Controllers\Controller> $controller */
        $this->action = [$controller, $method];

        return $this;
    }

    public function middleware(string $middleware): self
    {
        /** @var class-string<\PXP\Http\Middleware\Middleware> $middleware */
        $this->middlewares[] = $middleware;

        return $this;
    }

    /**
     * @return array<string, array<string, RouteAction>>
     */
    public static function listForTree(): array
    {
        $routes = [];

        foreach (self::$routes as $route) {
            if (! array_key_exists($route->route, $routes)) {
                $routes[$route->route] = [];
            }

            if ($route->action === null) {
                continue;
            }

            $action = new RouteAction;

            $action->class = $route->action[0];
            $action->method = $route->action[1];
            $action->middlewares = $route->middlewares;

            $routes[$route->route][$route->method] = $action;
        }

        return $routes;
    }

    public static function group(Route ...$routes): Group
    {
        return new Group(v(...$routes));
    }

    public static function findByName(string $name): Route
    {
        foreach (self::$routes as $route) {
            if ($route->name === $name) {
                return $route;
            }
        }

        throw new Exception("Route with name '$name' not found");
    }

    /**
     * @param  array<int|string, string|int>  $params
     */
    public function fillParams(array $params): string
    {
        $path = '';

        foreach (explode('/', trim($this->route, '/')) as $part) {
            if (str_starts_with($part, '{') && str_ends_with($part, '}')) {
                $param = substr($part, 1, -1);
                $value = $params[$param];
                $path .= "/$value";
            } else {
                $path .= "/$part";
            }
        }

        return $path;
    }
}
