<?php

namespace CommerceGuys\Addressing\Metadata;

/**
 * Interface for address formats.
 *
 * An address format provides metadata about storing and presenting addresses
 * for a single country.
 */
interface AddressFormatInterface
{
    // Fields.
    const FIELD_ADMINISTRATIVE_AREA = 'administrative_area';
    const FIELD_LOCALITY = 'locality';
    const FIELD_DEPENDENT_LOCALITY = 'dependent_locality';
    const FIELD_POSTAL_CODE = 'postal_code';
    const FIELD_SORTING_CODE = 'sorting_code';
    const FIELD_ADDRESS = 'address';
    const FIELD_ORGANIZATION = 'organization';
    const FIELD_RECIPIENT = 'recipient';

    // Administrative area types.
    const ADMINISTRATIVE_AREA_TYPE_AREA = 'area';
    const ADMINISTRATIVE_AREA_TYPE_COUNTY = 'county';
    const ADMINISTRATIVE_AREA_TYPE_DEPARTMENT = 'department';
    const ADMINISTRATIVE_AREA_TYPE_DISTRICT = 'district';
    const ADMINISTRATIVE_AREA_TYPE_DO_SI = 'do_si';
    const ADMINISTRATIVE_AREA_TYPE_EMIRATE = 'emirate';
    const ADMINISTRATIVE_AREA_TYPE_ISLAND = 'island';
    const ADMINISTRATIVE_AREA_TYPE_OBLAST = 'oblast';
    const ADMINISTRATIVE_AREA_TYPE_PARISH = 'parish';
    const ADMINISTRATIVE_AREA_TYPE_PREFECTURE = 'prefecture';
    const ADMINISTRATIVE_AREA_TYPE_PROVINCE = 'province';
    const ADMINISTRATIVE_AREA_TYPE_STATE = 'state';

    // Postal code types.
    const POSTAL_CODE_TYPE_POSTAL = 'postal';
    const POSTAL_CODE_TYPE_ZIP = 'zip';

    /**
     * Gets the two-letter country code.
     *
     * This is a CLDR country code, since CLDR includes additional countries
     * for addressing purposes, such as Canary Islands (IC).
     *
     * @return string The two-letter country code.
     */
    public function getCountryCode();

    /**
     * Sets the two-letter country code.
     *
     * @param string $countryCode The two-letter country code.
     */
    public function setCountryCode($countryCode);

    /**
     * Gets the format string.
     *
     * Defines the layout of an address, and consists of tokens (FIELD_
     * constants prefixed with a '%') separated by unix newlines (\n).
     * Example:
     * <code>
     * %recipient
     * %organization
     * %address
     * %locality %administrative_area %postal_code
     * </code>
     *
     * @return string The format string.
     */
    public function getFormat();

    /**
     * Sets the format string.
     *
     * @param string $format The format string.
     */
    public function setFormat($format);

    /**
     * Gets the list of required fields.
     *
     * @return array An array of FIELD_ constants.
     */
    public function getRequiredFields();

    /**
     * Sets the list of required fields.
     *
     * @param array $requiredFields An array of FIELD_ constants.
     */
    public function setRequiredFields(array $requiredFields);

    /**
     * Gets the list of fields that need to be uppercased.
     *
     * @return array An array of FIELD_ constants.
     */
    public function getUppercaseFields();

    /**
     * Sets the list of fields that need to be uppercased.
     *
     * @param array $uppercaseFields An array of FIELD_ constants.
     */
    public function setUppercaseFields(array $uppercaseFields);

    /**
     * Gets the administrative area type.
     *
     * Used for presenting the correct label to the end-user.
     *
     * @return string One of the ADMINISTRATIVE_AREA_TYPE_ constants.
     */
    public function getAdministrativeAreaType();

    /**
     * Sets the type of the administrative area.
     *
     * @return string One of the ADMINISTRATIVE_AREA_TYPE_ constants.
     */
    public function setAdministrativeAreaType($administrativeAreaType);

    /**
     * Gets the postal code type.
     *
     * Used for presenting the correct label to the end-user.
     *
     * @return string One of the POSTAL_CODE_TYPE_ constants.
     */
    public function getPostalCodeType();

    /**
     * Sets the postal code type.
     *
     * @param string $postalCodeType One of the POSTAL_CODE_TYPE_ constants.
     */
    public function setPostalCodeType($postalCodeType);

    /**
     * Gets the postal code pattern.
     *
     * This is a regular expression pattern used to validate postal codes.
     *
     * @return string|null The postal code pattern.
     */
    public function getPostalCodePattern();

    /**
     * Sets the postal code pattern.
     *
     * @param string $postalCodePattern The postal code pattern.
     */
    public function setPostalCodePattern($postalCodePattern);

    /**
     * Gets the postal code prefix.
     *
     * Applied to each postal code during formatting.
     *
     * @return string|null The postal code prefix.
     */
    public function getPostalCodePrefix();

    /**
     * Sets the postal code prefix.
     *
     * @param string $postalCodePrefix The postal code prefix.
     */
    public function setPostalCodePrefix($postalCodePrefix);
}
