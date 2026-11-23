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

        self::maintenance();

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

            $trace = str_repeat(' ', 8).str_replace("\n", "\n".str_repeat(' ', 8), $e->getTraceAsString());

            Log::log('Error '.$uid.' ('.$e::class.'): '
                .$e->getMessage().' in '.$e->getFile().' on line '.$e->getLine().":\n".$trace);

            return view('error', [
                'title' => 'Error',
                'error' => "An unknown error occured. Contact the admin. (Code $uid)",
            ])->output();
        }
    }

    private static function maintenance(): void
    {
        $maintenanceToken = perma('maintenance');

        if(! $maintenanceToken) {
            session(['maintenance' => false]);

            return;
        }

        // if token is given in url, save it to the session
        if (request('token') === $maintenanceToken) {
            session(['maintenance' => $maintenanceToken]);
        }

        // if session token is not available, block user
        if (session('maintenance') !== $maintenanceToken) {
            exit('
                <h1>Maintenance</h1>
                <p>This page is in maintenance mode. Please wait a few minutes and try again.</p>
            ');
        }
    }
}
