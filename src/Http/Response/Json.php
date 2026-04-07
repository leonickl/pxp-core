<?php

namespace PXP\Http\Response;

class Json extends Response
{
    private function __construct(private mixed $data, private int $flags) {}

    public static function of(mixed $data, int $flags = JSON_PRETTY_PRINT): Json
    {
        return new Json($data, $flags);
    }

    public function output(): string
    {
        return json_encode($this->data, $this->flags);
    }
}
