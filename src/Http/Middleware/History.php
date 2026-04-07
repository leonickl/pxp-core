<?php

namespace PXP\Http\Middleware;

use PXP\Lib\Auth;
use PXP\Router\Router;
use stdClass;

class History extends Middleware
{
    public function apply(object $route = new stdClass): mixed
    {
        if ($this->history($route)) {
            session([
                'history' => [$route, ...session()->array('history')],
            ]);
        }

        return true;
    }

    private function history(): bool
    {
        if ($route->history !== null) {
            return $route->history;
        }

        if ($route->method !== 'GET') {
            return false;
        }

        $path = explode('/', trim(Router::path(), '/'));

        if ($path[0] === 'css' || $path[0] === 'js' || $path[0] === 'img') {
            return false;
        }

        return true;
    }
}
