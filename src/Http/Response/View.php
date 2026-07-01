<?php

namespace PXP\Http\Response;

use LeoNickl\Plate\Plate;
use PXP\Exceptions\ViewNotFoundException;
use RuntimeException;
use Stringable;

class View extends Response implements Stringable
{
    /**
     * @param  array<string, mixed>  $params
     */
    private function __construct(
        private string $view,
        private array $params,
        private ?string $layout,
    ) {}

    /**
     * @param  array<string, mixed>  $params
     */
    public static function make(
        string $view,
        array $params = [],
        ?string $layout = 'app',
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
        foreach(modules() as $module => $_) {
            if (file_exists($path = path("views/$this->view.plate", module: $module))) {
                return $this->plateToPHP($path);
            }

            if (file_exists($path = path("views/$this->view.php", module: $module))) {
                return $path;
            }
        }

        throw new ViewNotFoundException($this->view);
    }

    private function render(): string
    {
        $path = $this->find();

        extract($this->params);

        ob_start();

        include $path;

        return ob_get_clean()
            ?: error(RuntimeException::class, 'Failed to capture view output');
    }

    private function layout(?string $view): View
    {
        if ($view === null) {
            return $this;
        }

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
        return $this->layout($this->layout)->render();
    }
}
