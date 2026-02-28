<?php

namespace PXP\Core\Lib;

class ErrorController
{
    public function notFound(string $route)
    {
        return view('error.not-found', compact('route'));
    }

    public function methodNotSupported(string $route, string $method)
    {
        return view('error.method-not-supported', compact('route', 'method'));
    }
}
