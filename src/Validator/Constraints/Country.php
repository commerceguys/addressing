<?php

namespace CommerceGuys\Addressing\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Country extends Constraint
{
    public $message = 'This value is not a valid country.';
}
