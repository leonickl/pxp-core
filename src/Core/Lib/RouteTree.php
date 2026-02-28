<?php

namespace PXP\Core\Lib;

class RouteTree
{
    private static array $params = [];

    private function __construct(private array $children, private array $methods) {}

    private static function empty()
    {
        return new self([], []);
    }

    public static function build(array $routes)
    {
        $tree = self::empty();

        foreach ($routes as $route => $content) {
            $split = explode('/', trim($route, '/'));

            $tree->children($split)->methods($content);
        }

        return $tree;
    }

    private function child(string $key): ?self
    {
        if (! array_key_exists($key, $this->children)) {
            $this->children[$key] = self::empty();
        }

        return $this->children[$key];
    }

    private function children(array $keys): ?self
    {
        if (count($keys) === 0 || count($keys) === 1 && $keys[0] === '') {
            return $this;
        }

        return $this
            ->child(array_shift($keys))
            ?->children($keys);
    }

    private function match(string|array $keys)
    {
        if (is_string($keys)) {
            foreach ($this->children as $key => $child) {
                if ($key === $keys) {
                    return $child;
                }

                if (str_starts_with($key, '{') && str_ends_with($key, '}')) {
                    $this->param([substr($key, 1, -1) => $keys]);

                    return $child;
                }
            }

            return null;
        }

        if (count($keys) === 0 || count($keys) === 1 && $keys[0] === '') {
            return $this;
        }

        return $this->match(array_shift($keys))?->match($keys);
    }

    private function methods(array $methods)
    {
        $this->methods = $methods;
    }

    private function toArray()
    {
        return [
            'methods' => $this->methods,
            'children' => c(...$this->children)->map(fn ($child) => $child->toArray())->toArray(),
        ];
    }

    public function dd()
    {
        dd($this->toArray());
    }

    public function find(string $route)
    {
        return $this->match(explode('/', trim($route, '/')));
    }

    public function method(?string $method = null)
    {
        return $method === null ? $this->methods : $this->methods[$method] ?? null;
    }

    public function param(null|string|array $param = null)
    {
        if (is_array($param)) {
            self::$params = [...self::$params, ...$param];
        }

        if (is_string($param)) {
            return self::$params[$param];
        }

        return self::$params;
    }
}
