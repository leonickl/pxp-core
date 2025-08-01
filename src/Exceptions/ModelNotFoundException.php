<?php

namespace PXP\Core\Exceptions;

class ModelNotFoundException extends \Exception
{
    public function __construct(string $model, int $id)
    {
        parent::__construct("Model $model with id $id not found");
    }
}
