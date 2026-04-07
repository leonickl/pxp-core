<?php

namespace PXP\Http\Response;

use PXP\Exceptions\ViewNotFoundException;
use Override;
use LeoNickl\Plate\Plate;

class View extends Response
{
    private function __construct(
        private string $view,
        private array $params,
        private string $layout,
    ) {}

    public static function make(
        string $view,
        array $params = [],
        string $layout = 'app',
    ): View {
        return new View($view, $params, $layout);
    }

    private function plateToPHP(string $plate): string
    {
        $hash = hash_file('sha256', $plate);
        $php = path("cache/plate/$hash.php");

        if (! file_exists('cache/plate')) {
            mkdir('cache/plate', recursive: true);
        }

        if (! file_exists($php)) {
            file_put_contents($php, Plate::file($plate));
        }
        
        return $php;
    }

    /**
     * find user-defined views at first, then internal ones.
     * try plate files before pure php files
     */
    private function find(): string
    {
        // user-defined views
        if (file_exists($user = path("views/$this->view.plate.php"))) {
            return $this->plateToPHP($user);
        }

        if (file_exists($user = path("views/$this->view.php"))) {
            return $user;
        }

        // framework-internal views
        if (file_exists($internal = path("views/$this->view.plate.php", internal: true))) {
            return $this->plateToPHP($internal);
        }

        if (file_exists($internal = path("views/$this->view.php", internal: true))) {
            return $internal;
        }

        throw new ViewNotFoundException($this->view);
    }

    public function render(): string
    {
        $path = $this->find();

        extract($this->params);

        ob_start();

        include $path;

        return ob_get_clean();
    }

    private function layout(string $view): View
    {
        return View::make($view, [
            'slot' => $this->render(),
        ]);
    }

    public function __toString()
    {
        return $this->render();
    }

    public function output(): string
    {
        return $this->layout($this->layout);
    }
}
