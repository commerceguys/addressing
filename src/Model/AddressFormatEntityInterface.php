<?php

namespace CommerceGuys\Addressing\Model;

interface AddressFormatEntityInterface extends AddressFormatInterface
{
    /**
     * Sets the two-letter country code.
     *
     * @param string $countryCode The two-letter country code.
     *
     * @return self
     */
    public function setCountryCode($countryCode);

    /**
     * Sets the format string.
     *
     * @param string $format The format string.
     *
     * @return self
     */
    public function setFormat($format);

    /**
     * Sets the list of required fields.
     *
     * @param array $requiredFields An array of address fields.
     *
     * @return self
     */
    public function setRequiredFields(array $requiredFields);

    /**
     * Sets the list of fields that need to be uppercased.
     *
     * @param array $uppercaseFields An array of address fields.
     *
     * @return self
     */
    public function setUppercaseFields(array $uppercaseFields);

    /**
     * Sets the administrative area type.
     *
     * @param string $administrativeAreaType The administrative area type.
     *
     * @return self
     */
    public function setAdministrativeAreaType($administrativeAreaType);

    /**
     * Sets the locality type.
     *
     * @param string $localityType The locality type.
     *
     * @return self
     */
    public function setLocalityType($localityType);

    /**
     * Sets the dependent locality type.
     *
     * @param string $dependentLocalityType The dependent locality type.
     *
     * @return self
     */
    public function setDependentLocalityType($dependentLocalityType);

    /**
     * Sets the postal code type.
     *
     * @param string $postalCodeType The postal code type.
     *
     * @return self
     */
    public function setPostalCodeType($postalCodeType);

    /**
     * Sets the postal code pattern.
     *
     * @param string $postalCodePattern The postal code pattern.
     *
     * @return self
     */
    public function setPostalCodePattern($postalCodePattern);

    /**
     * Sets the postal code prefix.
     *
     * @param string $postalCodePrefix The postal code prefix.
     *
     * @return self
     */
    public function setPostalCodePrefix($postalCodePrefix);
}
