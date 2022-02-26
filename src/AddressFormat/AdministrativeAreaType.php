<?php

namespace CommerceGuys\Addressing\AddressFormat;

use CommerceGuys\Addressing\AbstractEnum;

/**
 * Enumerates available administrative area types.
 *
 * @codeCoverageIgnore
 */
final class AdministrativeAreaType extends AbstractEnum
{
    const AREA = 'area';
    const CANTON = 'canton';
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

    public static function getDefault(): string
    {
        return static::PROVINCE;
    }
}
