<?php

namespace PXP\Http\Controllers;

use Exception;

/**
 * A Controller able to serve static file like css sheets
 * that are provided by the framework. To serve your own
 * files, just copy this controller and adjust it.
 */
class AssetController
{
    /**
     * Add this to your route definitions to serve css files:
     * Route::get('/css/{file}')->do(AssetController::class, 'css')
     */
    public function css(string $file): string
    {
        if (! preg_match('/^[a-zA-Z-]+$/', $file)) {
            throw new Exception("Invalid CSS path '$file'");
        }

        header('Content-Type: text/css');

        $content = file_get_contents($this->find($file));

        if (! $content) {
            throw new Exception("Error reading CSS file '$file'");
        }

        return $content;
    }

    private function find(string $file): string
    {
        $user = path("assets/css/$file.css");

        if (file_exists($user)) {
            return $user;
        }

        $internal = path("assets/css/$file.css", internal: true);

        if (file_exists($internal)) {
            return $internal;
        }

        throw new Exception("CSS file '$file' does not exist");
    }
}
