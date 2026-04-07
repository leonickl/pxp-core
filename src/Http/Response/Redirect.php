<?php

namespace PXP\Http\Response;

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

    public function output(): string
    {
        if ($this->data) {
            Session::setAll($this->data);
        }

        if (! preg_match('/^[a-zA-Z0-9\/-]+$/', $this->path)) {
            throw new Exception("Invalid redirect path '$this->path'");
        }

        header("location: $uri");

        return '';
    }
}
