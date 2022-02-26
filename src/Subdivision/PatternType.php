<?php

namespace CommerceGuys\Addressing\Subdivision;

use CommerceGuys\Addressing\AbstractEnum;

/**
 * Enumerates available pattern types.
 *
 * Determines whether preg_match() should match an entire string, or just a
 * part of it. Used for postal code validation.
 *
 * @codeCoverageIgnore
 * @deprecated since commerceguys/addressing 1.1.0.
 */
final class PatternType extends AbstractEnum
{
    const FULL = 'full';
    const START = 'start';

    /**
     * Gets the default value.
     *
     * @return string The default value.
     */
    public static function getDefault(): string
    {
        // Most subdivisions define only partial patterns.
        return static::START;
    }
}
