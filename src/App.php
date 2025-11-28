<?php

namespace PXP\Core;

use Exception;
use PXP\Core\Exceptions\UnauthorizedException;
use PXP\Core\Lib\Log;
use PXP\Core\Lib\Router;
use PXP\Core\Lib\Session;
use PXP\Core\Lib\View;

class App
{
    private static function authenticate()
    {
        $credentials = (array) config('credentials', []);

        if (! isset($_SERVER['PHP_AUTH_USER'])) {
            self::unauthorized();
        }

        if (! array_key_exists($_SERVER['PHP_AUTH_USER'], $credentials)) {
            self::unauthorized();
        }

        $password = $credentials[$_SERVER['PHP_AUTH_USER']];

        if ($password !== $_SERVER['PHP_AUTH_PW']) {
            self::unauthorized();
        }
    }

    private static function unauthorized()
    {
        header('WWW-Authenticate: Basic realm="My Protected Area"');
        header('HTTP/1.0 401 Unauthorized');

        throw new UnauthorizedException;
    }

    public static function run(bool $auth = false)
    {
        Session::start();

        $page = null;

        try {
            if ($auth) {
                self::authenticate();
            }

            $response = Router::route();

            if ($response instanceof View) {
                $page = $response->layout('app', [
                    'embedded' => request()->bool('embedded'),
                ])->render();
            } elseif (is_string($response)) {
                $page = $response;
            } else {
                $page = json_encode($response);
            }
        } catch (UnauthorizedException) {
            $page = View::make('error.unauthorized')->layout('app')->render();
        } catch (Throwable $e) {
            $class = $e::class;
            $msg = $e->getMessage();
            $file = $e->getFile();
            $line = $e->getLine();

            Log::log("Error ($class): $msg in $file on line $line");

            $page = View::make('error')->layout('app')->render();
        }

        return $page ?? null;
    }
}
