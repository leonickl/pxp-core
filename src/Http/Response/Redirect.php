<?php

namespace PXP\Http\Response;

use Exception;
use PXP\Lib\History;

class Redirect extends Response
{
    /**
     * @param array<string, mixed> $data
     */
    private function __construct(private string $path, private array $data) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function path(string $path, array $data = []): Redirect
    {
        return new Redirect($path, $data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function route(string $route, array $data = []): Redirect
    {
        return new Redirect(route($route), $data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function back(array $data = []): Redirect
    {
        return new Redirect((new History)->last(), $data);
    }

    public function output(): string
    {
        if ($this->data) {
            session($this->data);
        }

        if (! preg_match('/^[a-zA-Z0-9\/-]+$/', $this->path)) {
            throw new Exception("Invalid redirect path '$this->path'");
        }

        header("location: $this->path");

        return '';
    }
}
