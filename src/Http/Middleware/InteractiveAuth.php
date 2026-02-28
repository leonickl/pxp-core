<?php

namespace PXP\Http\Middleware;

use PXP\Core\Auth;
use PXP\Core\Lib\Session;
use PXP\Core\Middleware\Middleware;
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
