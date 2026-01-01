<?php

namespace PXP\Core\Lib;

class Router
{
    public static function route()
    {
        $uri = '/'.trim($_SERVER['REQUEST_URI'], "/\r\n\t ");

        $path = parse_url($uri, PHP_URL_PATH);
        $method = strtoupper($_REQUEST['__method'] ?? $_SERVER['REQUEST_METHOD']);

        $content = (object)self::find($path, $method);

        foreach($content->middlewares ?? [] as $middleware) {
            $status = (new $middleware)->apply();
            
            if($status !== true) {
                return $status;
            }
        }

        return (new ($content->class))->{$content->method}(...$content->params ?? []);  
    }

    private static function find(string $path, string $method)
    {
        $routes = Route::listForTree();

        $tree = RouteTree::build($routes);

        $endpoint = $tree->find($path);

        if ($endpoint === null || count($endpoint->method()) === 0) {
            return ['class' => \PXP\Core\Controllers\ErrorController::class, 'method' => 'notFound', 'params' => ['route' => $path]];
        }

        $action = $endpoint->method($method);

        if ($action === null) {
            return ['class' => \PXP\Core\Controllers\ErrorController::class, 'method' => 'methodNotSupported', 'params' => ['route' => $path, 'method' => $method]];
        }

        $action['params'] = array_merge($action['params'] ?? [], $tree->param());

        return $action;
    }

    public static function redirect(string $uri, array $data = [])
    {
        if ($data) {
            Session::setAll($data);
        }

        header("location: $uri");
    }
}
