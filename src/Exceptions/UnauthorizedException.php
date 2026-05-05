<?php

namespace PXP\Exceptions;

class UnauthorizedException extends DisplayException
{
    public function __construct(string $message = '')
    {
        parent::__construct('Unauthorized', $message);
    }
}
