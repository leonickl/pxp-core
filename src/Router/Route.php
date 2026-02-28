<?php

namespace PXP\Router;

class Route
{
    /**
     * store all defined routes
     */
    private static array $routes = [];

    private function __construct(
        readonly private string $route,
        readonly private string $method,

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

    public static function static(string $route): self
    {
        return self::get($route)
            ->do(ServeStatic::)
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
            ];
        }

        return $routes;
    }
}
