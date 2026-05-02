<?php

namespace PXP\Router;

use PXP\Http\Controllers\Controller;
use PXP\Http\Middleware\Middleware;

/**
 * @property class-string<Controller> $class
 * @property string $method
 * @property array<string, mixed> $params
 * @property list<class-string<Middleware>> $middlewares
 */
class RouteAction extends \stdClass {}
