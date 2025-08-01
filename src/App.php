<?php

namespace PXP\Core\Lib;

use PXP\Core\Exceptions\UnauthorizedException;
use Exception;

class App
{
    public static function run()
    {
        Session::start();

        try {
            $response = Router::route();

            $page = $response instanceof View
                ? $response->layout('app', [
                    'embedded' => request()->bool('embedded'),
                ])->render()
                : null;
        } catch (UnauthorizedException) {
            $page = \PXP\Core\Lib\View::make('error.unauthorized')->layout('app')->render();
        } catch (Exception $e) {
            $class = $e::class;
            $msg = $e->getMessage();
            $file = $e->getFile();
            $line = $e->getLine();

            Log::log("Error ($class): $msg in $file on line $line");

            $page = \PXP\Core\Lib\View::make('error')->layout('app')->render();
        }

        if ($page) {
            Session::stop();
        }

        return $page ?? null;
    }
}
