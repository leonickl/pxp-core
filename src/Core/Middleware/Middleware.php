<?php

namespace PXP\Core\Middleware;

abstract class Middleware
{
    /**
     * @return true if everything is okay; otherwise a result displayed to the user
     */
    abstract public function apply(): mixed;
}
