<?php

namespace PXP\Exceptions;

class ValidationException extends DisplayException
{
    public function __construct(string $message)
    {
        parent::__construct('Validation Exception', $message);
    }
}
