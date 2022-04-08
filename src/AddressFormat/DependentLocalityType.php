<?php

namespace CommerceGuys\Addressing\AddressFormat;

use CommerceGuys\Addressing\AbstractEnum;

/**
 * Enumerates available dependent locality types.
 *
 * @codeCoverageIgnore
 */
final class DependentLocalityType extends AbstractEnum
{
    const DISTRICT = 'district';
    const NEIGHBORHOOD = 'neighborhood';
    const VILLAGE_TOWNSHIP = 'village_township';
    const SUBURB = 'suburb';
    const TOWNLAND = 'townland';

    public static function getDefault(): string
    {
        return static::SUBURB;
    }
}
