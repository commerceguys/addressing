<?php

namespace CommerceGuys\Addressing\AddressFormat;

/**
 * Represents a set of field overrides.
 */
final class FieldOverrides
{
    /**
     * The hidden fields.
     *
     * @var string[]
     */
    protected $hiddenFields = [];

    /**
     * The optional fields.
     *
     * @var string[]
     */
    protected $optionalFields = [];

    /**
     * The required fields.
     *
     * @var string[]
     */
    protected $requiredFields = [];

    /**
     * Creates a new FieldOverrides instance.
     *
     * @param array $definition The field overrides, keyed by field name.
     */
    public function __construct(array $definition)
    {
        AddressField::assertAllExist(array_keys($definition));
        FieldOverride::assertAllExist($definition);

        foreach ($definition as $field => $override) {
            if ($override == FieldOverride::HIDDEN) {
                $this->hiddenFields[] = $field;
            } elseif ($override == FieldOverride::OPTIONAL) {
                $this->optionalFields[] = $field;
            } elseif ($override == FieldOverride::REQUIRED) {
                $this->requiredFields[] = $field;
            }
        }
    }

    /**
     * Gets the hidden fields.
     *
     * @return string[]
     */
    public function getHiddenFields()
    {
        return $this->hiddenFields;
    }

    /**
     * Gets the optional fields.
     *
     * @return string[]
     */
    public function getOptionalFields()
    {
        return $this->optionalFields;
    }

    /**
     * Gets the required fields.
     *
     * @return string[]
     */
    public function getRequiredFields()
    {
        return $this->requiredFields;
    }
}
