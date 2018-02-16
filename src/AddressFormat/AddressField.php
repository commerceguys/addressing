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
    const ADMINISTRATIVE_AREA = 'administrativeArea';
    const LOCALITY = 'locality';
    const DEPENDENT_LOCALITY = 'dependentLocality';
    const POSTAL_CODE = 'postalCode';
    const SORTING_CODE = 'sortingCode';
    const ADDRESS_LINE1 = 'addressLine1';
    const ADDRESS_LINE2 = 'addressLine2';
    const ORGANIZATION = 'organization';
    const GIVEN_NAME = 'givenName';
    const ADDITIONAL_NAME = 'additionalName';
    const FAMILY_NAME = 'familyName';

    /**
     * Gets the tokens (values prefixed with %).
     *
     * @return array An array of tokens, keyed by constant.
     */
    public static function getTokens()
    {
        $tokens = array_map(function ($field) {
            return '%' . $field;
        }, static::getAll());

        return $tokens;
    }
}
