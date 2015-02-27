<?php

namespace CommerceGuys\Addressing\Enum;

use CommerceGuys\Enum\AbstractEnum;

/**
 * Enumerates available address fields.
 *
 * @codeCoverageIgnore
 */
final class AddressField extends AbstractEnum
{
    const ADMINISTRATIVE_AREA = 'administrative_area';
    const LOCALITY = 'locality';
    const DEPENDENT_LOCALITY = 'dependent_locality';
    const POSTAL_CODE = 'postal_code';
    const SORTING_CODE = 'sorting_code';
    const ADDRESS = 'address';
    const ORGANIZATION = 'organization';
    const RECIPIENT = 'recipient';
}
