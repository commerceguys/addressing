<?php

namespace CommerceGuys\Addressing\Exception;

/**
 * Thrown when a value does not match the expected type.
 *
 * @codeCoverageIgnore
 */
class UnexpectedTypeException extends \InvalidArgumentException implements ExceptionInterface
{
    public function __construct($value, $expectedType)
    {
        $givenType = is_object($value) ? get_class($value) : gettype($value);
        parent::__construct(sprintf('Expected argument of type "%s", "%s" given', $expectedType, $givenType));
    }
}
