<?php

namespace PXP\Http\Middleware;

use PXP\Lib\Auth;
use PXP\Lib\Session;
use PXP\Http\Middleware\Middleware;
use PXP\Http\Middleware;

class InteractiveAuth extends Middleware
{
    public function apply(): mixed
    {
        return Auth::auth() ? true : view('login', [
            'errors' => Session::take('errors', []),
        ]);
    }
}
