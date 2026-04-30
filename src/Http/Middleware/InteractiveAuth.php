<?php

namespace PXP\Http\Middleware;

use PXP\Http\Response\View;
use PXP\Lib\Auth;

class InteractiveAuth extends Middleware
{
    public function apply(): true|View
    {
        return Auth::auth() ? true : view('login', [
            'errors' => session()->take('errors', []),
        ]);
    }
}
