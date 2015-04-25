<?php

namespace CommerceGuys\Addressing\Enum;

use CommerceGuys\Enum\AbstractEnum;

/**
 * Enumerates available pattern types.
 *
 * Determines whether preg_match() should match an entire string, or just a
 * part of it. Used for postal code validation.
 *
 * @codeCoverageIgnore
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
    public static function getDefault()
    {
        // Most subdivisions define only partial patterns.
        return static::START;
    }
}
