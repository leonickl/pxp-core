<?php

namespace PXP\Lib;

use PXP\Exceptions\DisplayException;
use PXP\Http\Response\Response;
use PXP\Router\Router;
use RuntimeException;
use Throwable;

class App
{
    public static function run(): string
    {
        session_start();

        try {
            $response = Router::route();

            if ($response instanceof Response) {
                return $response->output();
            }

            if (is_string($response)) {
                return $response;
            }

            return json_encode($response)
                ?: error(RuntimeException::class, 'JSON encoding failed');
        } catch (DisplayException $e) {
            return view('error', [
                'title' => $e->getTitle(),
                'message' => $e->getMessage(),
            ])->output();
        } catch (Throwable $e) {
            $uid = uniqid();

            Log::log('Error '.$uid.' ('.$e::class.'): '
                .$e->getMessage().' in '.$e->getFile().' on line '.$e->getLine());

            return view('error', [
                'title' => 'Error',
                'error' => "An unknown error occured. Contact the admin. (Code $uid)",
            ])->output();
        }
    }
}
