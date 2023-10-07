<?php

namespace CommerceGuys\Addressing\AddressFormat;

use CommerceGuys\Addressing\AbstractEnum;

/**
 * Enumerates available locality types.
 *
 * @codeCoverageIgnore
 */
final class LocalityType extends AbstractEnum
{
    public const CITY = 'city';
    public const DISTRICT = 'district';
    public const POST_TOWN = 'post_town';
    public const SUBURB = 'suburb';
    public const TOWN_CITY = 'town_city';

    public static function getDefault(): string
    {
        return LocalityType::CITY;
    }
}
