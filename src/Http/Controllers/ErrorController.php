<?php

namespace PXP\Http\Controllers;

use PXP\Http\Response\Response;

class ErrorController extends Controller
{
    public function notFound(string $route): Response
    {
        return view('error.not-found', compact('route'));
    }

    public function methodNotSupported(string $route, string $method): Response
    {
        return view('error.method-not-supported', compact('route', 'method'));
    }
}
