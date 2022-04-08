<?php

namespace CommerceGuys\Addressing\AddressFormat;

use CommerceGuys\Addressing\AbstractEnum;

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

    public static function getDefault(): string
    {
        return static::POSTAL;
    }
}
