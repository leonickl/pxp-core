<?php

namespace PXP\Data;

class PermamentVariable
{
    public function __construct(private string $name) {}

    private function file(): string
    {
        return path("database/.$this->name.var");
    }

    public function get(mixed $default = null): mixed
    {
        if (! file_exists($this->file())) {
            return $default;
        }

        $contents = file_get_contents($this->file());

        return $contents === false
            ? $default
            : unserialize($contents);
    }

    public function set(mixed $value): void
    {
        file_put_contents($this->file(), serialize($value));
    }
}
