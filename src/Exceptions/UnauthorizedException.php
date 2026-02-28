<?php

namespace PXP\Exceptions;

class UnauthorizedException extends \Exception
{
    public function __construct()
    {
        parent::__construct('Unauthorized');
    }
}
