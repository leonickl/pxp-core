<?php

namespace PXP\Http\Response;

use PXP\Exceptions\ViewNotFoundException;

class View extends Response
{
    protected function __construct(
        protected string $view,
        protected array $params,
        protected string $layout,
    ) {}

    public static function make(
        string $view,
        array $params = [],
        string $layout = 'app',
    ): self {
        return new self($view, $params);
    }

    protected function find(): string
    {
        // user-defined views
        $user = path("views/$this->view.php");

        if (file_exists($user)) {
            return $user;
        }

        // framework-internal views
        $internal = path("views/$this->view.php", internal: true);

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

    public function output(): string
    {
        return $this->layout($this->layout);
    }
}
