<?php

namespace AnourValar\LaravelAtom\Exceptions;

class InternalValidationException extends \Exception
{
    /**
     * @var array
     */
    protected $context = [];

    /**
     * @param string $message
     * @param mixed $code
     * @param mixed $previous
     * @return void
     */
    public function __construct($message = 'Internal validation error.', $code = null, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Create an instance from the existing ValidationException
     *
     * @param \Illuminate\Validation\ValidationException $e
     * @return self
     */
    public static function fromValidationException(\Illuminate\Validation\ValidationException $e): self
    {
        $context = ['errors' => $e->validator->errors()->all(), 'data' => $e->validator->getData()];

        return (new static('Internal validation error: ' . $e->getMessage(), $e->getCode(), $e))->setContext($context);
    }

    /**
     * Set: Log Context
     *
     * @param array $context
     * @return self
     */
    public function setContext(array $context): self
    {
        $this->context = $context;

        return $this;
    }

    /**
     * Get: Log Context
     * @see Laravel Error Handling
     *
     * @return array
     */
    public function context(): array
    {
        return $this->context;
    }
}
