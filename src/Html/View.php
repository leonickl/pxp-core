<?php

namespace PXP\Html;

use PXP\Exceptions\ViewNotFoundException;

class View
{
    private function __construct(protected string $view, protected array $params) {}

    public static function make(string $view, array $params = []): self
    {
        return new self($view, $params);
    }

    private function find(): string
    {
        // user-defined views
        $user = path("views/$this->view.php");

        if (file_exists($user)) {
            return $user;
        }

        // framework-internal views
        $internal = __DIR__."/../../views/$this->view.php";

        if (file_exists($internal)) {
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

    public function layout(string $view)
    {
        return self::make($view, [
            'slot' => $this->render(),
        ]);
    }

    public function __toString()
    {
        return $this->render();
    }
}
