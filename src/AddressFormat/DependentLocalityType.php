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
    public const DISTRICT = 'district';
    public const NEIGHBORHOOD = 'neighborhood';
    public const VILLAGE_TOWNSHIP = 'village_township';
    public const SUBURB = 'suburb';
    public const TOWNLAND = 'townland';

    public static function getDefault(): string
    {
        return DependentLocalityType::SUBURB;
    }
}
