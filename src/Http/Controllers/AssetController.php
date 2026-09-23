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

        $content = file_get_contents($this->find($file, 'css'));

        if (! $content) {
            throw new Exception("Error reading CSS file '$file'");
        }

        return $content;
    }

    /**
     * Add this to your route definitions to serve js files:
     * Route::get('/js/{file}')->do(AssetController::class, 'js')
     */
    public function js(string $file): string
    {
        if (! preg_match('/^[a-zA-Z-]+$/', $file)) {
            throw new Exception("Invalid JS path '$file'");
        }

        header('Content-Type: application/js');

        $content = file_get_contents($this->find($file, 'js'));

        if (! $content) {
            throw new Exception("Error reading JS file '$file'");
        }

        return $content;
    }

    private function find(string $file, string $type): string
    {
        foreach (modules() as $module => $_) {
            if (file_exists($path = path("assets/$type/$file.$type", module: $module))) {
                return $path;
            }
        }

        throw new Exception("$type file '$file' does not exist");
    }
}
