<?php

namespace PXP\Http\Middleware;

use PXP\Http\Middleware;
use PXP\Lib\Auth;
use PXP\Lib\Session;

class InteractiveAuth extends Middleware
{
    public function apply(): mixed
    {
        return Auth::auth() ? true : view('login', [
            'errors' => Session::take('errors', []),
        ]);
    }
}
