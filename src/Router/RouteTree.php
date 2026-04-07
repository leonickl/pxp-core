<?php

namespace PXP\Router;

class RouteTree
{
    /**
     * @var array<string, string>
     */
    private static array $params = [];

    /**
     * @param array<string, RouteTree|null> $children
     * @param array<mixed, mixed> $methods // TODO: fix types
     */
    private function __construct(private array $children, private array $methods) {}

    private static function empty(): self
    {
        return new self(children: [], methods: []);
    }

    /**
     * @param array<string, array<string, array{
     *     'class': class-string<\PXP\Http\Controllers\Controller>,
     *     'method': string,
     *     'middlewares': list<class-string<\PXP\Http\Middleware\Middleware>>,
     *     'history': bool|null
     * }>> $routes
     */
    public static function build(array $routes): self
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
        // match a url part
        if (is_string($keys)) {
            // at first match fully specified paths
            foreach ($this->children as $key => $child) {
                if ($key === $keys) {
                    return $child;
                }
            }

            // then try with path arguments
            foreach ($this->children as $key => $child) {
                if (str_starts_with($key, '{') && str_ends_with($key, '}')) {
                    $this->param([substr($key, 1, -1) => $keys]);

                    return $child;
                }
            }

            // or and don't find anything :/
            return null;
        }

        // arrived at end of url
        if (count($keys) === 0 || count($keys) === 1 && $keys[0] === '') {
            return $this;
        }

        // match first url part and then the rest
        return $this->match(array_shift($keys))?->match($keys);
    }

    private function methods(array $methods)
    {
        $this->methods = $methods;
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
