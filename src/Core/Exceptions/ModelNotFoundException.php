<?php

namespace PXP\Core\Exceptions;

class ModelNotFoundException extends \Exception
{
    public function __construct(string $model, string $column, mixed $value)
    {
        parent::__construct("Model $model with $column=$value not found");
    }
}
