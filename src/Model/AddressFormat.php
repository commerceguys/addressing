<?php

namespace CommerceGuys\Addressing\Model;

use CommerceGuys\Addressing\Enum\AddressField;
use CommerceGuys\Addressing\Enum\AdministrativeAreaType;
use CommerceGuys\Addressing\Enum\DependentLocalityType;
use CommerceGuys\Addressing\Enum\LocalityType;
use CommerceGuys\Addressing\Enum\PostalCodeType;

/**
 * Default address format implementation.
 *
 * Can be mapped and used by Doctrine, for implementing applications that
 * want to allow address formats to be user editable.
 */
class AddressFormat implements AddressFormatEntityInterface
{
    use FormatStringTrait;

    /**
     * The country code.
     *
     * @var string
     */
    protected $countryCode;

    /**
     * The required fields.
     *
     * @var array
     */
    protected $requiredFields;

    /**
     * The fields that need to be uppercased.
     *
     * @var string
     */
    protected $uppercaseFields;

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
     * The locale.
     *
     * @var string
     */
    protected $locale;

    /**
     * {@inheritdoc}
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * {@inheritdoc}
     */
    public function setCountryCode($countryCode)
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRequiredFields()
    {
        return $this->requiredFields;
    }

    /**
     * {@inheritdoc}
     */
    public function setRequiredFields(array $requiredFields)
    {
        AddressField::assertAllExist($requiredFields);
        $this->requiredFields = $requiredFields;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUppercaseFields()
    {
        return $this->uppercaseFields;
    }

    /**
     * {@inheritdoc}
     */
    public function setUppercaseFields(array $uppercaseFields)
    {
        AddressField::assertAllExist($uppercaseFields);
        $this->uppercaseFields = $uppercaseFields;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAdministrativeAreaType()
    {
        return $this->administrativeAreaType;
    }

    /**
     * {@inheritdoc}
     */
    public function setAdministrativeAreaType($administrativeAreaType)
    {
        AdministrativeAreaType::assertExists($administrativeAreaType);
        $this->administrativeAreaType = $administrativeAreaType;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLocalityType()
    {
        return $this->localityType;
    }

    /**
     * {@inheritdoc}
     */
    public function setLocalityType($localityType)
    {
        LocalityType::assertExists($localityType);
        $this->localityType = $localityType;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDependentLocalityType()
    {
        return $this->dependentLocalityType;
    }

    /**
     * {@inheritdoc}
     */
    public function setDependentLocalityType($dependentLocalityType)
    {
        DependentLocalityType::assertExists($dependentLocalityType);
        $this->dependentLocalityType = $dependentLocalityType;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPostalCodeType()
    {
        return $this->postalCodeType;
    }

    /**
     * {@inheritdoc}
     */
    public function setPostalCodeType($postalCodeType)
    {
        PostalCodeType::assertExists($postalCodeType);
        $this->postalCodeType = $postalCodeType;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPostalCodePattern()
    {
        return $this->postalCodePattern;
    }

    /**
     * {@inheritdoc}
     */
    public function setPostalCodePattern($postalCodePattern)
    {
        $this->postalCodePattern = $postalCodePattern;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPostalCodePrefix()
    {
        return $this->postalCodePrefix;
    }

    /**
     * {@inheritdoc}
     */
    public function setPostalCodePrefix($postalCodePrefix)
    {
        $this->postalCodePrefix = $postalCodePrefix;

        return $this;
    }

    /**
     * Gets the locale.
     *
     * @return string The locale.
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Sets the locale.
     *
     * @param string $locale The locale.
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }
}
