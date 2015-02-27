<?php

namespace CommerceGuys\Addressing\Enum;

use CommerceGuys\Enum\AbstractEnum;

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

    /**
     * Returns the default value.
     */
    public static function getDefault()
    {
        return static::CITY;
    }
}
