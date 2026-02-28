<?php

namespace PXP\Console;

use Closure;

class Command
{
    private static array $commands = [];

    private function __construct(private string $command, private Closure $action) {}

    public static function new(string $command, Closure $action): self
    {
        $new = new self($command, $action);
        self::$commands[$command] = $new;

        return $new;
    }

    public static function resolve(string $command): ?Closure
    {
        foreach (self::$commands as $name => $object) {
            if ($name === $command) {
                return $object->action;
            }
        }

        return null;
    }
}
