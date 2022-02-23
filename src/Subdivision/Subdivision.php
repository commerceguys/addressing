<?php

namespace CommerceGuys\Addressing\Subdivision;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Represents a country subdivision.
 *
 * Subdivisions are hierarchical and can have up to three levels:
 * Administrative Area -> Locality -> Dependent Locality.
 */
class Subdivision
{
    /**
     * The parent.
     *
     * @var Subdivision|null
     */
    protected $parent;

    /**
     * The country code.
     *
     * @var string
     */
    protected $countryCode;

    /**
     * The subdivision code.
     *
     * @var string
     */
    protected $code;

    /**
     * The local subdivision code.
     *
     * @var string|null
     */
    protected $localCode;

    /**
     * The subdivision name.
     *
     * @var string
     */
    protected $name;

    /**
     * The local subdivision name.
     *
     * @var string|null
     */
    protected $localName;

    /**
     * The subdivision iso code.
     *
     * @var string|null
     */
    protected $isoCode;

    /**
     * The postal code pattern.
     *
     * @var string|null
     */
    protected $postalCodePattern;

    /**
     * The postal code pattern type.
     *
     * @var string
     */
    protected $postalCodePatternType;

    /**
     * The children.
     *
     * @param Subdivision[]
     */
    protected $children;

    /**
     * The locale.
     *
     * @var string|null
     */
    protected $locale;

    /**
     * Creates a new Subdivision instance.
     *
     * @param array $definition The definition array.
     */
    public function __construct(array $definition)
    {
        // Validate the presence of required properties.
        $requiredProperties = [
            'country_code', 'code', 'name',
        ];
        foreach ($requiredProperties as $requiredProperty) {
            if (empty($definition[$requiredProperty])) {
                throw new \InvalidArgumentException(sprintf('Missing required property %s.', $requiredProperty));
            }
        }
        // Add defaults for properties that are allowed to be empty.
        $definition += [
            'parent' => null,
            'locale' => null,
            'local_code' => null,
            'local_name' => null,
            'iso_code' => null,
            'postal_code_pattern' => null,
            'postal_code_pattern_type' => PatternType::getDefault(),
            'children' => new ArrayCollection(),
        ];

        $this->parent = $definition['parent'];
        $this->countryCode = $definition['country_code'];
        $this->locale = $definition['locale'];
        $this->code = $definition['code'];
        $this->localCode = $definition['local_code'];
        $this->name = $definition['name'];
        $this->localName = $definition['local_name'];
        $this->isoCode = $definition['iso_code'];
        $this->postalCodePattern = $definition['postal_code_pattern'];
        $this->postalCodePatternType = $definition['postal_code_pattern_type'];
        $this->children = $definition['children'];
    }

    /**
     * Gets the subdivision parent.
     *
     * @return Subdivision|null The parent, or NULL if there is none.
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Gets the subdivision country code.
     *
     * This is a CLDR country code, since CLDR includes additional countries
     * for addressing purposes, such as Canary Islands (IC).
     *
     * @return string The two-letter country code.
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * Gets the subdivision locale.
     *
     * Used for selecting local subdivision codes/names. Only defined if the
     * subdivision has a local code or name.
     *
     * @return string|null The subdivision locale, if defined.
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Gets the subdivision code.
     *
     * Represents the subdivision on the formatted address.
     * Could be an abbreviation, such as "CA" for California, or a full string
     * such as "Grand Cayman".
     *
     * This is the value that is stored on the address object.
     * Guaranteed to be in latin script.
     *
     * @return string The subdivision code.
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Gets the subdivision local code.
     *
     * When a country uses a non-latin script, the local code is the code
     * in that script (Cyrilic in Russia, Chinese in China, etc).
     *
     * @return string|null The subdivision local code, if defined.
     */
    public function getLocalCode()
    {
        return $this->localCode;
    }

    /**
     * Gets the subdivision name.
     *
     * Represents the subdivision in dropdowns.
     * Guaranteed to be in latin script.
     *
     * @return string The subdivision name.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Gets the subdivision local name.
     *
     * When a country uses a non-latin script, the local name is the name
     * in that script (Cyrilic in Russia, Chinese in China, etc).
     *
     * @return string|null The subdivision local name, if defined.
     */
    public function getLocalName()
    {
        return $this->localName;
    }

    /**
     * Gets the subdivision ISO 3166-2 code.
     *
     * Only defined for administrative areas. Examples: 'US-CA', 'JP-01'.
     *
     * @return string|null The subdivision ISO 3166-2 code.
     */
    public function getIsoCode()
    {
        return $this->isoCode;
    }

    /**
     * Gets the postal code pattern.
     *
     * This is a regular expression pattern used to validate postal codes.
     *
     * @return string|null The postal code pattern.
     *
     * @deprecated since commerceguys/addressing 1.1.0.
     */
    public function getPostalCodePattern()
    {
        return $this->postalCodePattern;
    }

    /**
     * Gets the postal code pattern type.
     *
     * @return string|null The postal code pattern type.
     *
     * @deprecated since commerceguys/addressing 1.1.0.
     */
    public function getPostalCodePatternType()
    {
        return $this->postalCodePatternType;
    }

    /**
     * Gets the subdivision children.
     *
     * @return Subdivision[] The subdivision children.
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Checks whether the subdivision has children.
     *
     * @return bool TRUE if the subdivision has children, FALSE otherwise.
     */
    public function hasChildren()
    {
        return !$this->children->isEmpty();
    }
}
