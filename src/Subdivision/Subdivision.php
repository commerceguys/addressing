<?php

namespace CommerceGuys\Addressing\Subdivision;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Represents a country subdivision.
 *
 * Subdivisions are hierarchical and can have up to three levels:
 * Administrative Area -> Locality -> Dependent Locality.
 */
class Subdivision
{
    protected ?Subdivision $parent;

    protected string $countryCode;

    protected string $id;

    protected string $code;

    protected ?string $localCode = null;

    protected string $name;

    protected ?string $localName = null;

    protected ?string $postalCodePattern = null;

    /**
     * The children.
     *
     * @param Subdivision[]
     */
    protected Collection $children;

    protected ?string $locale = null;

    /**
     * Creates a new Subdivision instance.
     *
     * @param array $definition The definition array.
     */
    public function __construct(array $definition)
    {
        // Validate the presence of required properties.
        $requiredProperties = [
            'country_code', 'id', 'code', 'name',
        ];
        foreach ($requiredProperties as $requiredProperty) {
            if (empty($definition[$requiredProperty])) {
                throw new \InvalidArgumentException(sprintf('Missing required property %s.', $requiredProperty));
            }
        }

        $this->parent = $definition['parent'] ?? null;
        $this->countryCode = $definition['country_code'];
        $this->id = $definition['id'];
        $this->locale = $definition['locale'] ?? null;
        $this->code = $definition['code'];
        $this->localCode = $definition['local_code'] ?? null;
        $this->name = $definition['name'];
        $this->localName = $definition['local_name'] ?? null;
        $this->postalCodePattern = $definition['postal_code_pattern'] ?? null;
        $this->children = $definition['children'] ?? new ArrayCollection();
    }

    /**
     * Gets the subdivision parent.
     *
     * @return Subdivision|null The parent, or NULL if there is none.
     */
    public function getParent(): ?Subdivision
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
    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    /**
     * Gets the subdivision id.
     *
     * This is an ISO code when available (e.g. "CA" for the US state of California),
     * in which case it consists of up to 3 alphanumeric characters.
     * Otherwise it matches the subdivision name and could be of any length.
     *
     * @return string The subdivision id.
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Gets the subdivision locale.
     *
     * Used for selecting local subdivision codes/names. Only defined if the
     * subdivision has a local code or name.
     *
     * @return string|null The subdivision locale, if defined.
     */
    public function getLocale(): ?string
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
     * Guaranteed to be in latin script.
     */
    public function getCode(): string
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
    public function getLocalCode(): ?string
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
    public function getName(): string
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
    public function getLocalName(): ?string
    {
        return $this->localName;
    }

    /**
     * Gets the postal code pattern.
     *
     * This is a regular expression pattern used to validate postal codes.
     * Used instead of the address-format-level pattern when defined.
     */
    public function getPostalCodePattern(): ?string
    {
        return $this->postalCodePattern;
    }

    /**
     * Gets the subdivision children.
     *
     * @return Collection The subdivision children.
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    /**
     * Checks whether the subdivision has children.
     */
    public function hasChildren(): bool
    {
        return !$this->children->isEmpty();
    }
}
