<?php

namespace CommerceGuys\Addressing\AddressFormat;

use CommerceGuys\Addressing\AbstractEnum;

/**
 * Enumerates available address fields.
 *
 * @codeCoverageIgnore
 */
final class AddressField extends AbstractEnum
{
    // The values match the address property names.
    public const ADMINISTRATIVE_AREA = 'administrativeArea';
    public const LOCALITY = 'locality';
    public const DEPENDENT_LOCALITY = 'dependentLocality';
    public const POSTAL_CODE = 'postalCode';
    public const SORTING_CODE = 'sortingCode';
    public const ADDRESS_LINE1 = 'addressLine1';
    public const ADDRESS_LINE2 = 'addressLine2';
    public const ADDRESS_LINE3 = 'addressLine3';
    public const ORGANIZATION = 'organization';
    public const GIVEN_NAME = 'givenName';
    public const ADDITIONAL_NAME = 'additionalName';
    public const FAMILY_NAME = 'familyName';

    /**
     * Gets the tokens (values prefixed with %).
     *
     * @return array An array of tokens, keyed by constant.
     * @throws \ReflectionException
     */
    public static function getTokens(): array
    {
        return array_map(static function ($field) {
            return '%' . $field;
        }, AddressField::getAll());
    }
}
