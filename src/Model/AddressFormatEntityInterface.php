<?php

namespace CommerceGuys\Addressing\Model;

interface AddressFormatEntityInterface extends AddressFormatInterface
{
    /**
     * Sets the two-letter country code.
     *
     * @param string $countryCode The two-letter country code.
     */
    public function setCountryCode($countryCode);

    /**
     * Sets the format string.
     *
     * @param string $format The format string.
     */
    public function setFormat($format);

    /**
     * Sets the list of required fields.
     *
     * @param array $requiredFields An array of address fields.
     */
    public function setRequiredFields(array $requiredFields);

    /**
     * Sets the list of fields that need to be uppercased.
     *
     * @param array $uppercaseFields An array of address fields.
     */
    public function setUppercaseFields(array $uppercaseFields);

    /**
     * Sets the administrative area type.
     *
     * @param string $administrativeAreaType The administrative area type.
     */
    public function setAdministrativeAreaType($administrativeAreaType);

    /**
     * Sets the locality type.
     *
     * @param string $localityType The locality type.
     */
    public function setLocalityType($localityType);

    /**
     * Sets the dependent locality type.
     *
     * @param string $dependentLocalityType The dependent locality type.
     */
    public function setDependentLocalityType($dependentLocalityType);

    /**
     * Sets the postal code type.
     *
     * @param string $postalCodeType The postal code type.
     */
    public function setPostalCodeType($postalCodeType);

    /**
     * Sets the postal code pattern.
     *
     * @param string $postalCodePattern The postal code pattern.
     */
    public function setPostalCodePattern($postalCodePattern);

    /**
     * Sets the postal code prefix.
     *
     * @param string $postalCodePrefix The postal code prefix.
     */
    public function setPostalCodePrefix($postalCodePrefix);
}
