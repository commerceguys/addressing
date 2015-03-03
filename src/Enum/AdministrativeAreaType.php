<?php

namespace CommerceGuys\Addressing\Enum;

use CommerceGuys\Enum\AbstractEnum;

/**
 * Enumerates available administrative area types.
 *
 * @codeCoverageIgnore
 */
final class AdministrativeAreaType extends AbstractEnum
{
    const AREA = 'area';
    const COUNTY = 'county';
    const DEPARTMENT = 'department';
    const DISTRICT = 'district';
    const DO_SI = 'do_si';
    const EMIRATE = 'emirate';
    const ISLAND = 'island';
    const OBLAST = 'oblast';
    const PARISH = 'parish';
    const PREFECTURE = 'prefecture';
    const PROVINCE = 'province';
    const STATE = 'state';

    /**
     * Gets the default value.
     *
     * @return string The default value.
     */
    public static function getDefault()
    {
        return static::PROVINCE;
    }
}
