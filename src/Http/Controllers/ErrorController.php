<?php

namespace PXP\Http\Controllers;

use PXP\Http\Response\Response;

class ErrorController extends Controller
{
    public function notFound(string $route): Response
    {
        return view('error', [
            'title' => 'Not Found',
            'message' => "$route not found.",
        ]);
    }

    public function methodNotSupported(string $route, string $method): Response
    {
        return view('error', [
            'title' => 'Method not supported',
            'message' => "Method $method is not supported on $route.",
        ]);
    }
}
