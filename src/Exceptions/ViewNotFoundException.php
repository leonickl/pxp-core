<?php

namespace PXP\Exceptions;

class ViewNotFoundException extends \Exception
{
    public function __construct(string $view)
    {
        parent::__construct("View '$view' not found");
    }
}
