<?php

namespace PXP\Router;

use Exception;

class Route
{
    /**
     * store all defined routes
     */
    private static array $routes = [];

    private function __construct(
        private readonly string $route,
        private readonly string $method,

        private ?string $name = null,
        private ?array $action = null,
        private ?bool $history = null,

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
        $this->action = [$controller, $method];

        return $this;
    }

    public function history(bool $history)
    {
        $this->history = $history;

        return $this;
    }

    public function middleware(string $middleware): self
    {
        $this->middlewares[] = $middleware;

        return $this;
    }

    public static function listForTree(): array
    {
        $routes = [];

        foreach (self::$routes as $route) {
            if (! array_key_exists($route->route, $routes)) {
                $routes[$route->route] = [];
            }

            $routes[$route->route][$route->method] = [
                'class' => $route->action[0],
                'method' => $route->action[1],
                'middlewares' => $route->middlewares,
                'history' => $route->history,
            ];
        }

        return $routes;
    }

    public static function group(Route ...$routes)
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

    public function fillParams(array $params): string
    {
        $path = '';

        foreach (explode('/', trim($this->route, '/')) as $part) {
            if (str_starts_with($part, '{') && str_ends_with($part, '}')) {
                $param = substr($part, 1, -1);
                $value = $params[$param];
                $path .= "/$value";
            }

            $path .= "/$part";
        }

        return $path;
    }
}
