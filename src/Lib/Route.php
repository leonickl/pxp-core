<?php

namespace PXP\Core\Lib;

use PXP\Core\Middleware\Middleware;

class Route
{
    private static array $routes = [];

    private ?string $name = null;

    private ?array $action = null;

    private array $middlewares = [];

    private function __construct(private string $route, private string $method) {}

    private static function register(string $route, string $method)
    {
        $new = new self($route, $method);

        self::$routes[] = $new;

        return $new;
    }

    public static function listForTree()
    {
        $routes = [];

        foreach (self::$routes as $route) {
            if (! array_key_exists($route->route, $routes)) {
                $routes[$route->route] = [];
            }

            $routes[$route->route][$route->method] = ['class' => $route->action[0], 'method' => $route->action[1], 'middlewares' => $route->middlewares];
        }

        return $routes;
    }

    public static function get(string $route)
    {
        return self::register($route, 'GET');
    }

    public static function post(string $route)
    {
        return self::register($route, 'POST');
    }

    public function name(string $name)
    {
        $this->name = $name;
        return $this;
    }

    public function do(string $controller, string $method)
    {
        $this->action = [$controller, $method];
        return $this;
    }

    public function middleware(string $middleware)
    {
        $this->middlewares[] = $middleware;
        return $this;
    }
}
