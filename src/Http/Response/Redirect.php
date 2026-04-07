<?php

namespace PXP\Http\Response;

use Exception;
use PXP\Lib\History;

class Redirect extends Response
{
    private function __construct(private string $path, private array $data) {}

    public static function path(string $path, array $data = []): Redirect
    {
        return new Redirect($path, $data);
    }

    public static function route(string $route, array $data = []): Redirect
    {
        return new Redirect(route($route), $data);
    }

    public static function back(int $steps = 1, array $data = []): Redirect
    {
        return new Redirect((new History)->back($steps), $data);
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
