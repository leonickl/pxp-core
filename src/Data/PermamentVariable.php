<?php

namespace PXP\Data;

class PermamentVariable
{
    public function __construct(private string $name) {}

    private function file()
    {
        return path("database/.$this->name.var");
    }

    public function get(mixed $default = null)
    {
        if (! file_exists($this->file())) {
            return $default;
        }

        return unserialize(file_get_contents($this->file()));
    }

    public function set(mixed $value)
    {
        file_put_contents($this->file(), serialize($value));
    }
}
