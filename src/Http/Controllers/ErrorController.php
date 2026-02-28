<?php

namespace PXP\Http\Controllers;

class ErrorController extends Controller
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
