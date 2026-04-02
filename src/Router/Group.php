<?php

namespace PXP\Router;

use PXP\Ds\Vector;

class Group
{
    public function __construct(private Vector $routes) {}

    public function middleware(string $middleware): self
    {
        foreach ($this->routes as $route) {
            $route->middleware($middleware);
        }

        return $this;
    }
}
