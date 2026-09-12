<?php

namespace PXP\Data\Validate;

use Closure;
use Exception;
use PXP\Exceptions\ValidationException;

/**
 * @method self string()
 * @method self int()
 * @method self float()
 * @method self array()
 * @method self nullable()
 * @method self min(int $min = 1)
 * @method self max(int $max = 100)
 * @method self in(mixed ...$list)
 * @method self email()
 */
class Validator
{
    private ?string $type = null;

    private bool $nullable = false;

    /**
     * @var list<Guard>
     */
    private array $guards = [];

    private Closure $mutator;

    private bool $throw = true;

    private ?array $errors = null;

    public function __construct(private mixed $var, private string $name) {}

    /**
     * @param  list<mixed>  $args
     */
    public function __call(string $method, array $args): self
    {
        if (in_array($method, ['string', 'int', 'float', 'array', 'enum'])) {
            $this->type = $method;

            if ($this->type === 'string') {
                $this->guards[] = new Guard(
                    fn () => $this->var === null || is_string($this->var),
                    fn () => "$this->name must be of type $this->type",
                );
            }

            if ($method === 'int' || $method === 'float') {
                $this->guards[] = new Guard(
                    fn () => $this->var === null || is_numeric($this->var),
                    fn () => "$this->name must be numeric",
                );

                $this->mutator = fn () => "{$this->type}val"($this->var);
            }

            if ($this->type === 'array') {
                $this->guards[] = new Guard(
                    fn () => $this->var === null || is_array($this->var),
                    fn () => "$this->name must be of type $this->type",
                );
            }

            if ($method === 'enum') {
                if (! isset($args[0])) {
                    throw new Exception('pass an enum to the validator');
                }

                if (! enum_exists($args[0])) {
                    throw new Exception('pass a valid enum to the validator');
                }

                $this->guards[] = new Guard(
                    fn () => $this->var === null || $args[0]::tryFrom($this->var) !== null,
                    fn () => "$this->name must be a valid case of ".$args[0],
                );

                $this->mutator = fn () => $args[0]::tryFrom($this->var);
            }

            return $this;
        }

        if (! isset($this->type)) {
            throw new Exception('set a type first (string|int|float|array|enum)');
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

            throw new Exception("'min' can only be used for string|int|float");
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

            throw new Exception("'max' can only be used for string|int|float");
        }

        if ($method === 'in') {
            if ($this->type === 'string') {
                $this->guards[] = new Guard(
                    fn () => in_array($this->var, $args),
                    fn () => "$this->name must be one of ".implode(', ', $args),
                );

                return $this;
            }

            throw new Exception("'in' can only be used for type string");
        }

        if ($method === 'email') {
            if ($this->type === 'string') {
                $this->guards[] = new Guard(
                    fn () => filter_var($this->var, FILTER_VALIDATE_EMAIL),
                    fn () => "$this->name must be an email address",
                );

                return $this;
            }

            throw new Exception("'email' can only be used for type string");
        }

        throw new Exception("unknown validation rule $method");
    }

    /**
     * @return list<ValidationException>
     */
    private function validate(): array
    {
        $errors = [];

        if (! $this->nullable) {
            $this->guards[] = new Guard(
                fn () => isset($this->var),
                fn () => "$this->name must not be null",
            );
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

    private function errors(): array
    {
        if (! isset($this->errors)) {
            $this->errors = $this->validate();
        }

        return $this->errors;
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

    public function var(): mixed
    {
        return [$this->name => isset($this->mutator) ? ($this->mutator)($this->var) : $this->var];
    }
}
