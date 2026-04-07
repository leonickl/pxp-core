<?php

namespace PXP\Http\Middleware;

use PXP\Lib\Auth;

class InteractiveAuth extends Middleware
{
    public function apply(): mixed
    {
        return Auth::auth() ? true : view('login', [
            'errors' => session()->take('errors', []),
        ]);
    }
}
