<?php

namespace CommerceGuys\Addressing\Validator\Constraints;

use CommerceGuys\Addressing\Enum\AddressField;
use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 *
 * @codeCoverageIgnore
 */
class AddressFormat extends Constraint
{
    public $fields;
    public $blankMessage = 'This value should be blank';
    public $notBlankMessage = 'This value should not be blank';
    public $invalidMessage = 'This value is invalid.';

    /**
     * {@inheritdoc}
     */
    public function __construct($options = null)
    {
        parent::__construct($options);

        // Validate all fields by default.
        if (empty($this->fields)) {
            $this->fields = AddressField::getAll();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
