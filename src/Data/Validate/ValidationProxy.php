<?php

namespace PXP\Data\Validate;

use PXP\Lib\Arrays;

class ValidationProxy
{
    public function __construct(private Arrays $arrays) {}

    public function __get(string $property): Validator
    {
        return validate($this->arrays->get($property), $property);
    }
}
