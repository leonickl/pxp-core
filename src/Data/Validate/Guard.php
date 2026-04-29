<?php

namespace PXP\Data\Validate;

use Closure;
use PXP\Exceptions\ValidationException;

class Guard
{
    public function __construct(private Closure $guard, private Closure $error) {}

    public function check()
    {
        if (! ($this->guard)()) {
            throw new ValidationException(($this->error)());
        }
    }
}
