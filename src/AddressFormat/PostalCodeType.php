<?php

namespace CommerceGuys\Addressing\AddressFormat;

use CommerceGuys\Enum\AbstractEnum;

/**
 * Enumerates available postal code types.
 *
 * @codeCoverageIgnore
 */
final class PostalCodeType extends AbstractEnum
{
    const EIR = 'eircode';
    const PIN = 'pin';
    const POSTAL = 'postal';
    const ZIP = 'zip';

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
