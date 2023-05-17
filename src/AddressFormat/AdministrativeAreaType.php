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
    public const AREA = 'area';
    public const CANTON = 'canton';
    public const COUNTY = 'county';
    public const DEPARTMENT = 'department';
    public const DISTRICT = 'district';
    public const DO_SI = 'do_si';
    public const EMIRATE = 'emirate';
    public const ISLAND = 'island';
    public const PARISH = 'parish';
    public const PREFECTURE = 'prefecture';
    public const PROVINCE = 'province';
    public const REGION = 'region';
    public const STATE = 'state';

    public static function getDefault(): string
    {
        return AdministrativeAreaType::PROVINCE;
    }
}
