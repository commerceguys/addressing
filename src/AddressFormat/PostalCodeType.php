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
    public const EIR = 'eircode';
    public const PIN = 'pin';
    public const POSTAL = 'postal';
    public const ZIP = 'zip';

    public static function getDefault(): string
    {
        return PostalCodeType::POSTAL;
    }
}
