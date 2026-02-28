<?php

namespace PXP\Core;

use PXP\Core\Lib\Log;
use PXP\Core\Lib\Router;
use PXP\Core\Lib\Session;
use PXP\Core\Lib\View;
use PXP\Exceptions\UnauthorizedException;
use PXP\Exceptions\ValidationException;
use Throwable;

class App
{
    public static function run(bool $auth = false): string
    {
        Session::start();

        try {
            $response = Router::route();

            if ($response instanceof View) {
                return $response->layout('app')->render();
            }

            if (is_string($response)) {
                return $response;
            }

            return json_encode($response);
        } catch (UnauthorizedException) {
            return View::make('error.unauthorized')->layout('app')->render();
        } catch (ValidationException $e) {
            return View::make('error.validation', ['error' => $e->getMessage()])
                ->layout('app')
                ->render();
        } catch (Throwable $e) {
            Log::log('Error ('.$e::class.'): '
                .$e->getMessage().' in '.$e->getFile().' on line '.$e->getLine());

            return View::make('error')->layout('app')->render();
        }
    }
}
