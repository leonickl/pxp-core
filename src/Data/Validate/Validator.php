<?php

namespace PXP\Data\Validate;

use Exception;
use PXP\Exceptions\ValidationException;

/**
 * @method self string()
 * @method self int()
 * @method self float()
 * @method self min(int $min = 1)
 * @method self max(int $max = 100)
 */
class Validator
{
    private ?string $type = null;

    private bool $nullable = false;

    /**
     * @var list<Guard>
     */
    private array $guards = [];

    private bool $throw = true;

    public function __construct(private mixed $var, private string $name) {}

    /**
     * @param  list<mixed>  $args
     */
    public function __call(string $method, array $args): self
    {
        if (in_array($method, ['string', 'int', 'float', 'array'])) {
            $this->type = $method;

            return $this;
        }

        if ($method === 'nullable') {
            $this->nullable = true;

            return $this;
        }

        if ($method === 'min') {
            $min = $args[0] ?? 1;

            if ($this->type === 'string') {
                $len = strlen($this->var);

                $this->guards[] = new Guard(
                    fn () => $len >= $min,
                    fn () => "$this->name must be at least $min characters long, only $len given",
                );

                return $this;
            }

            if ($this->type === 'int' || $this->type === 'float') {
                $this->guards[] = new Guard(
                    fn () => $this->var >= $min,
                    fn () => "$this->name must be at least $min, $this->var given",
                );

                return $this;
            }
        }

        if ($method === 'max') {
            $max = $args[0] ?? 100;

            if ($this->type === 'string') {
                $len = strlen($this->var);

                $this->guards[] = new Guard(
                    fn () => $len <= $max,
                    fn () => "$this->name must be at most $max characters long, $len given",
                );

                return $this;
            }

            if ($this->type === 'int' || $this->type === 'float') {
                $this->guards[] = new Guard(
                    fn () => $this->var <= $max,
                    fn () => "$this->name must be at most $max, $this->var given",
                );

                return $this;
            }
        }

        if ($method === 'in') {
            if ($this->type === 'string') {
                $this->guards[] = new Guard(
                    fn () => in_array($this->var, $args),
                    fn () => "$this->name must be one of ".implode(', ', $args),
                );

                return $this;
            }

            throw new Exception("'in' not valid for type '$this->type'");
        }

        throw new Exception("unknown validation rule $method");
    }

    /**
     * @return list<ValidationException>
     */
    private function errors(): array
    {
        $errors = [];

        if ($this->var === null) {
            if ($this->nullable) {
                return [];
            } else {
                return [
                    new ValidationException("$this->name must not be null"),
                ];
            }
        }

        /** @phpstan-ignore callable.nonCallable */
        if (! "is_$this->type"($this->var)) {
            $errors[] = new ValidationException("$this->name must be of type $this->type");
        }

        foreach ($this->guards as $guard) {
            try {
                $guard->check();
            } catch (ValidationException $e) {
                $errors[] = $e;
            }
        }

        return $errors;
    }

    /**
     * @return list<ValidationException>
     */
    public function get(): array
    {
        $this->throw = false;

        return $this->errors();
    }

    public function __destruct()
    {
        if ($this->throw) {
            foreach ($this->errors() as $error) {
                throw $error;
            }
        }
    }
}
