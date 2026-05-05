<?php

namespace PXP\Exceptions;

use Exception;

class DisplayException extends Exception
{
    public function __construct(private string $title, string $message)
    {
        parent::__construct($message);
    }

    public function getTitle(): string
    {
        return $this->title;
    }
}
