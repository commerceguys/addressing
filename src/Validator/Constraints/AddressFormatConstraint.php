<?php

namespace CommerceGuys\Addressing\Validator\Constraints;

use CommerceGuys\Addressing\AddressFormat\AddressField;
use CommerceGuys\Addressing\AddressFormat\FieldOverride;
use CommerceGuys\Addressing\AddressFormat\FieldOverrides;
use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 *
 * @codeCoverageIgnore
 */
class AddressFormatConstraint extends Constraint
{
    public ?FieldOverrides $fieldOverrides = null;

    public bool $validatePostalCode = true;

    public string $blankMessage = 'This value should be blank';

    public string $notBlankMessage = 'This value should not be blank';

    public string $invalidMessage = 'This value is invalid.';

    /**
     * {@inheritdoc}
     * @throws \ReflectionException
     */
    public function __construct($options = null)
    {
        parent::__construct($options);

        // Ensure there's always a FieldOverrides object.
        $this->fieldOverrides = $this->fieldOverrides ?: new FieldOverrides([]);
    }

    /**
     * @return string|string[]
     */
    public function getTargets(): array|string
    {
        return self::CLASS_CONSTRAINT;
    }
}
