<?php

namespace CommerceGuys\Addressing\Enum;

use CommerceGuys\Enum\AbstractEnum;

/**
 * Enumerates available locality types.
 *
 * @codeCoverageIgnore
 */
final class PostalCodeType extends AbstractEnum
{
    const POSTAL = 'postal';
    const ZIP = 'zip';
    const PIN = 'pin';

    /**
     * Returns the default value.
     */
    public static function getDefault()
    {
        return static::POSTAL;
    }
}
