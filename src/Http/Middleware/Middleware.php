<?php

namespace PXP\Http\Middleware;

abstract class Middleware
{
    /**
     * Returns true if everything is okay, otherwise
     * a result to be displayed to the user.
     */
    abstract public function apply(): mixed;
}
