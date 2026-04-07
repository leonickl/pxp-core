<?php

namespace PXP\Http\Middleware;

use PXP\Router\Router;
use stdClass;

class History extends Middleware
{
    public function apply(object $route = new stdClass): mixed
    {
        if ($this->history($route->history)) {
            session([
                'history' => [Router::path(), ...session()->array('history')],
            ]);
        }

        return true;
    }

    private function history(?bool $preset): bool
    {
        if ($preset !== null) {
            return $preset;
        }

        if (Router::method() !== 'GET') {
            return false;
        }

        $path = explode('/', trim(Router::path(), '/'));

        if ($path[0] === 'css' || $path[0] === 'js' || $path[0] === 'img') {
            return false;
        }

        return true;
    }
}
