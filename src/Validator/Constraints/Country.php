<?php

namespace CommerceGuys\Address\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
class Country extends Constraint
{
    public $message = 'The country "%string%" is invalid.'
}
