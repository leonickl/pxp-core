<?php

namespace PXP\Router;

use PXP\Ds\Vector;

class GlobalGroup
{
    private array $preMiddlewares = [];
    private array $postMiddlewares = [];

    private static ?self $instance = null;

    private function __construct() {}

    public static function make(): self
    {
        if (self::$instance === null) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    public function preMiddleware(string $middleware): self
    {
        $this->preMiddlewares[] = $middleware;
        return $this;
    }

    public function postMiddleware(string $middleware): self
    {
        $this->postMiddlewares[] = $middleware;
        return $this;
    }

    public function preMiddlewares(): array
    {
        return $this->preMiddlewares;
    }

    public function postMiddlewares(): array
    {
        return $this->postMiddlewares;
    }
}
