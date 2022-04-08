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
    const CITY = 'city';
    const DISTRICT = 'district';
    const POST_TOWN = 'post_town';
    const SUBURB = 'suburb';

    public static function getDefault(): string
    {
        return static::CITY;
    }
}
