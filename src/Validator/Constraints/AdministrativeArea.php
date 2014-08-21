<?php

namespace CommerceGuys\Address\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
class AdministrativeArea extends Constraint
{
    public $message = 'The administrative area "%string%" is invalid.'
}
