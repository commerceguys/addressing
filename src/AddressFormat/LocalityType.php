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

    /**
     * Gets the default value.
     *
     * @return string The default value.
     */
    public static function getDefault()
    {
        return static::CITY;
    }
}
