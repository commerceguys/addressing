<?php

namespace CommerceGuys\Addressing\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class CountryConstraint extends Constraint
{
    public $message = 'This value is not a valid country.';
}
