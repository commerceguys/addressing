<?php

namespace CommerceGuys\Addressing\AddressFormat;

use CommerceGuys\Addressing\AbstractEnum;

/**
 * Enumerates available address field overrides.
 *
 * Used to instruct validators and form builders to ignore the
 * address format for specific fields, e.g. to make the company
 * field always required, name fields always be hidden, etc.
 *
 * Note that a "required" field override will only apply
 * if the address format uses the field.
 *
 * @codeCoverageIgnore
 */
final class FieldOverride extends AbstractEnum
{
    const HIDDEN = 'hidden';
    const OPTIONAL = 'optional';
    const REQUIRED = 'required';
}
