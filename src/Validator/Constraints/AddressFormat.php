<?php

namespace CommerceGuys\Addressing\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class AddressFormat extends Constraint
{
    public $blankMessage = 'This value should be blank';
    public $notBlankMessage = 'This value should not be blank';
    public $invalidMessage = 'This value is invalid.';

    /**
     * {@inheritDoc}
     * @codeCoverageIgnore
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
