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
    public static function run()
    {
        Session::start();

        $page = null;

        try {
            $response = Router::route();

            if($response instanceof View) {
                $page = $response->layout('app', [
                    'embedded' => request()->bool('embedded'),
                ])->render();
            } elseif(is_string($response)) {
                $page = $response;
            } else {
                $page = json_encode($response);
            }
        } catch (UnauthorizedException) {
            $page = View::make('error.unauthorized')->layout('app')->render();
        } catch (Exception $e) {
            $class = $e::class;
            $msg = $e->getMessage();
            $file = $e->getFile();
            $line = $e->getLine();

            Log::log("Error ($class): $msg in $file on line $line");

            $page = View::make('error')->layout('app')->render();
        }

        if ($page) {
            Session::stop();
        }

        return $page ?? null;
    }
}
