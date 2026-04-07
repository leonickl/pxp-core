<?php

namespace PXP\Http\Response;

abstract class Response
{
    abstract public function output(): string;
}
