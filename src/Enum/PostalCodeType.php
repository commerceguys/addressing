<?php

namespace CommerceGuys\Addressing\Enum;

use CommerceGuys\Enum\AbstractEnum;

/**
 * Enumerates available postal code types.
 *
 * @codeCoverageIgnore
 */
final class PostalCodeType extends AbstractEnum
{
    const POSTAL = 'postal';
    const ZIP = 'zip';
    const PIN = 'pin';

    /**
     * Gets the default value.
     *
     * @return string The default value.
     */
    public static function getDefault()
    {
        return static::POSTAL;
    }
}
