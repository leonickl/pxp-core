<?php

namespace PXP\Http\Response;

use PXP\Exceptions\ViewNotFoundException;

abstract class Template extends Response
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
    ): static {
        return new static($view, $params, $layout);
    }

    abstract protected function find(): string;

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
        return static::make($view, [
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
