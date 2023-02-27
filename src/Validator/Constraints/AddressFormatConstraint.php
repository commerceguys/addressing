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
    /**
     * Used fields.
     *
     * @deprecated Use $fieldOverrides instead.
     */
    public array $fields = [];

    public ?FieldOverrides $fieldOverrides = null;

    /**
     * Whether extended postal code validation is enabled.
     *
     * Extended postal code validation uses subdivision-level patterns to
     * in addition to the country-level pattern supplied by the address format.
     *
     * This feature is deprecated, commerceguys/addressing 2.0 will only
     * perform country-level validation.
     */
    public bool $extendedPostalCodeValidation = true;

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

        // Convert used fields into field overrides.
        if (!empty($this->fields)) {
            $unusedFields = array_diff(AddressField::getAll(), $this->fields);
            $definition = [];
            foreach ($unusedFields as $field) {
                $definition[$field] = FieldOverride::HIDDEN;
            }
            $this->fieldOverrides = new FieldOverrides($definition);
        }

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
