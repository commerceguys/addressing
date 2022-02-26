<?php

namespace CommerceGuys\Addressing\AddressFormat;

/**
 * Provides metadata for storing and presenting a country's addresses.
 */
class AddressFormat
{
    /**
     * The country code.
     *
     * @var string
     */
    protected $countryCode;

    /**
     * The locale.
     *
     * @var string
     */
    protected $locale;

    /**
     * The format string.
     *
     * @var string
     */
    protected $format;

    /**
     * The local format string.
     *
     * @var string
     */
    protected $localFormat;

    /**
     * The used fields.
     *
     * @var array
     */
    protected $usedFields = [];

    /**
     * The used fields, grouped by line.
     *
     * @var array
     */
    protected $groupedFields = [];

    /**
     * The required fields.
     *
     * @var array
     */
    protected $requiredFields = [];

    /**
     * The fields that need to be uppercased.
     *
     * @var string
     */
    protected $uppercaseFields = [];

    /**
     * The administrative area type.
     *
     * @var string
     */
    protected $administrativeAreaType;

    /**
     * The locality type.
     *
     * @var string
     */
    protected $localityType;

    /**
     * The dependent locality type.
     *
     * @var string
     */
    protected $dependentLocalityType;

    /**
     * The postal code type.
     *
     * @var string
     */
    protected $postalCodeType;

    /**
     * The postal code pattern.
     *
     * @var string
     */
    protected $postalCodePattern;

    /**
     * The postal code prefix.
     *
     * @var string
     */
    protected $postalCodePrefix;

    /**
     * The subdivision depth.
     *
     * @var int
     */
    protected $subdivisionDepth;

    public function __construct(array $definition)
    {
        // Validate the presence of required properties.
        foreach (['country_code', 'format'] as $requiredProperty) {
            if (empty($definition[$requiredProperty])) {
                throw new \InvalidArgumentException(sprintf('Missing required property %s.', $requiredProperty));
            }
        }
        // Add defaults for properties that are allowed to be empty.
        $definition += [
            'locale' => null,
            'local_format' => null,
            'required_fields' => [],
            'uppercase_fields' => [],
            'postal_code_pattern' => null,
            'postal_code_prefix' => null,
            'subdivision_depth' => 0,
        ];
        AddressField::assertAllExist($definition['required_fields']);
        AddressField::assertAllExist($definition['uppercase_fields']);
        $this->countryCode = $definition['country_code'];
        $this->locale = $definition['locale'];
        $this->format = $definition['format'];
        $this->localFormat = $definition['local_format'];
        $this->requiredFields = $definition['required_fields'];
        $this->uppercaseFields = $definition['uppercase_fields'];
        $this->subdivisionDepth = $definition['subdivision_depth'];

        $usedFields = $this->getUsedFields();
        if (in_array(AddressField::ADMINISTRATIVE_AREA, $usedFields)) {
            if (isset($definition['administrative_area_type'])) {
                AdministrativeAreaType::assertExists($definition['administrative_area_type']);
                $this->administrativeAreaType = $definition['administrative_area_type'];
            }
        }
        if (in_array(AddressField::LOCALITY, $usedFields)) {
            if (isset($definition['locality_type'])) {
                LocalityType::assertExists($definition['locality_type']);
                $this->localityType = $definition['locality_type'];
            }
        }
        if (in_array(AddressField::DEPENDENT_LOCALITY, $usedFields)) {
            if (isset($definition['dependent_locality_type'])) {
                DependentLocalityType::assertExists($definition['dependent_locality_type']);
                $this->dependentLocalityType = $definition['dependent_locality_type'];
            }
        }
        if (in_array(AddressField::POSTAL_CODE, $usedFields)) {
            if (isset($definition['postal_code_type'])) {
                PostalCodeType::assertExists($definition['postal_code_type']);
                $this->postalCodeType = $definition['postal_code_type'];
            }
            $this->postalCodePattern = $definition['postal_code_pattern'];
            $this->postalCodePrefix = $definition['postal_code_prefix'];
        }
    }

    /**
     * Gets the two-letter country code.
     *
     * This is a CLDR country code, since CLDR includes additional countries
     * for addressing purposes, such as Canary Islands (IC).
     *
     * @return string The two-letter country code.
     */
    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    /**
     * Gets the locale.
     *
     * Only defined if the country has a local format.
     *
     * @return string|null The locale, if defined.
     */
    public function getLocale(): ?string
    {
        return $this->locale;
    }

    /**
     * Gets the format string.
     *
     * Defines the layout of an address, and consists of tokens (address fields
     * prefixed with a '%') separated by unix newlines (\n).
     * Example:
     * <code>
     * %givenName %familyName
     * %organization
     * %addressLine1
     * %addressLine2
     * %locality %administrativeArea %postalCode
     * </code>
     *
     * @return string The format string.
     */
    public function getFormat(): string
    {
        return $this->format;
    }

    /**
     * Gets the local format string.
     *
     * Defined for countries that use a different ordering of fields when the
     * address is entered in the native script. For example, China uses a
     * major-to-minor format (country first, name last) when the address
     * is entered in Chinese.
     *
     * @return string|null The local format string, if defined.
     */
    public function getLocalFormat(): ?string
    {
        return $this->localFormat;
    }

    /**
     * Gets the list of used fields.
     *
     * @return array An array of address fields.
     */
    public function getUsedFields(): array
    {
        if (empty($this->usedFields)) {
            $this->usedFields = [];
            foreach (AddressField::getAll() as $field) {
                if (strpos($this->format, '%' . $field) !== false) {
                    $this->usedFields[] = $field;
                }
            }
        }

        return $this->usedFields;
    }

    /**
     * Gets the list of used subdivision fields.
     *
     * @return array An array of address fields.
     */
    public function getUsedSubdivisionFields(): array
    {
        $fields = [
            AddressField::ADMINISTRATIVE_AREA,
            AddressField::LOCALITY,
            AddressField::DEPENDENT_LOCALITY,
        ];
        // Remove fields not used by the format, and reset the keys.
        $fields = array_intersect($fields, $this->getUsedFields());
        $fields = array_values($fields);

        return $fields;
    }

    /**
     * Gets the list of required fields.
     *
     * @return array An array of address fields.
     */
    public function getRequiredFields(): array
    {
        return $this->requiredFields;
    }

    /**
     * Gets the list of fields that need to be uppercased.
     *
     * @return array An array of address fields.
     */
    public function getUppercaseFields()
    {
        return $this->uppercaseFields;
    }

    /**
     * Gets the administrative area type.
     *
     * Used for presenting the correct label to the end-user.
     *
     * @return string|null The administrative area type, or null if the
     *                     administrative area field isn't used.
     */
    public function getAdministrativeAreaType(): ?string
    {
        return $this->administrativeAreaType;
    }

    /**
     * Gets the locality type.
     *
     * Used for presenting the correct label to the end-user.
     *
     * @return string|null The locality type, or null if the locality field
     *                     isn't used.
     */
    public function getLocalityType(): ?string
    {
        return $this->localityType;
    }

    /**
     * Gets the dependent locality type.
     *
     * Used for presenting the correct label to the end-user.
     *
     * @return string|null The dependent locality type, or null if the
     *                     dependent locality field isn't used.
     */
    public function getDependentLocalityType(): ?string
    {
        return $this->dependentLocalityType;
    }

    /**
     * Gets the postal code type.
     *
     * Used for presenting the correct label to the end-user.
     *
     * @return string|null The postal code type, or null if the postal code
     *                     field isn't used.
     */
    public function getPostalCodeType(): ?string
    {
        return $this->postalCodeType;
    }

    /**
     * Gets the postal code pattern.
     *
     * This is a regular expression pattern used to validate postal codes.
     * Ignored if a subdivision defines its own full postal code pattern
     * (E.g. Hong Kong when specified as a Chinese province).
     *
     * @return string|null The postal code pattern.
     */
    public function getPostalCodePattern(): ?string
    {
        return $this->postalCodePattern;
    }

    /**
     * Gets the postal code prefix.
     *
     * The prefix is optional and added to postal codes only when formatting
     * an address for international mailing, as recommended by postal services.
     *
     * @return string|null The postal code prefix.
     */
    public function getPostalCodePrefix(): ?string
    {
        return $this->postalCodePrefix;
    }

    /**
     * Gets the subdivision depth.
     *
     * Indicates the number of levels of predefined subdivisions.
     *
     * Note that a country might use a subdivision field without having
     * predefined subdivisions for it.
     * For example, if the locality field is used by the address format, but
     * the subdivision depth is 1, that means that the field element should be
     * rendered as a textbox, since there's no known data to put in a dropdown.
     *
     * It is also possible to have no subdivisions for specific parents, even
     * though the country generally has predefined subdivisions at that depth.
     *
     * @return int The subdivision depth. Possible values:
     *             0: no subdivisions have been predefined.
     *             1: administrative areas.
     *             2: administrative areas, localities.
     *             3: administrative areas, localities, dependent localities.
     */
    public function getSubdivisionDepth(): int
    {
        return $this->subdivisionDepth;
    }
}
