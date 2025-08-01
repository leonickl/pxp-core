<?php

namespace PXP\Core\Lib;

class View
{

    private function __construct(protected string $view, protected array $params) {}

    public static function make(string $view, array $params = []): self
    {
        return new self($view, $params);
    }

    public function render(): string
    {
        $internal = "../../views/$this->view.php";

        $user = path("views/$this->view.php");

        if(file_exists($internal)) {
            $path = $internal;
        }

        if(file_exists($user)) {
            $path = $user;
        }

        if (! isset($path)) {
            throw new \PXP\Core\Exceptions\ViewNotFoundException($user);
        }

        extract($this->params);

        ob_start();
        include $path;

        return ob_get_clean();
    }

    public function layout(string $view, array $additionalParams = [])
    {
        $this->params = [...$this->params, ...$additionalParams];

        return self::make($view, [
            'slot' => $this->render(),
            ...$additionalParams,
        ]);
    }
}
