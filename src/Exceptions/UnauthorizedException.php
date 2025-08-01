<?php

namespace PXP\Core\Exceptions;

class UnauthorizedException extends \Exception
{
    public function __construct()
    {
        parent::__construct('Unauthorized');
    }
}
