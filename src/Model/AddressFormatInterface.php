<?php

namespace CommerceGuys\Addressing\Model;

/**
 * Interface for address formats.
 *
 * An address format provides metadata about storing and presenting addresses
 * for a single country.
 */
interface AddressFormatInterface
{
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
     * Defines the layout of an address, and consists of tokens (address fields
     * prefixed with a '%') separated by unix newlines (\n).
     * Example:
     * <code>
     * %recipient
     * %organization
     * %addressLine1
     * %addressLine2
     * %locality %administrativeArea %postalCode
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
     * Gets the list of used fields.
     *
     * @return array An array of address fields.
     */
    public function getUsedFields();

    /**
     * Gets the list of used fields, grouped by line.
     *
     * @return array An array of address fields grouped by line, in the same
     *               order as they appear in the format string. For example:
     *               [
     *                 ['recipient'],
     *                 ['organization'],
     *                 [addressLine1],
     *                 [addressLine2],
     *                 [locality, administrativeArea, postalCode]
     *               ]
     */
    public function getGroupedFields();

    /**
     * Gets the list of required fields.
     *
     * @return array An array of address fields.
     */
    public function getRequiredFields();

    /**
     * Sets the list of required fields.
     *
     * @param array $requiredFields An array of address fields.
     */
    public function setRequiredFields(array $requiredFields);

    /**
     * Gets the list of fields that need to be uppercased.
     *
     * @return array An array of address fields.
     */
    public function getUppercaseFields();

    /**
     * Sets the list of fields that need to be uppercased.
     *
     * @param array $uppercaseFields An array of address fields.
     */
    public function setUppercaseFields(array $uppercaseFields);

    /**
     * Gets the administrative area type.
     *
     * Used for presenting the correct label to the end-user.
     *
     * @return string|null The administrative area type, or null if the
     *                     administrative area field isn't used.
     */
    public function getAdministrativeAreaType();

    /**
     * Sets the administrative area type.
     *
     * @param string $administrativeAreaType The administrative area type.
     */
    public function setAdministrativeAreaType($administrativeAreaType);

    /**
     * Gets the locality type.
     *
     * Used for presenting the correct label to the end-user.
     *
     * @return string|null The locality type, or null if the locality field
     *                     isn't used.
     */
    public function getLocalityType();

    /**
     * Sets the locality type.
     *
     * @param string $localityType The locality type.
     */
    public function setLocalityType($localityType);

    /**
     * Gets the dependent locality type.
     *
     * Used for presenting the correct label to the end-user.
     *
     * @return string|null The dependent locality type, or null if the
     *                     dependent locality field isn't used.
     */
    public function getDependentLocalityType();

    /**
     * Sets the dependent locality type.
     *
     * @param string $dependentLocalityType The dependent locality type.
     */
    public function setDependentLocalityType($dependentLocalityType);

    /**
     * Gets the postal code type.
     *
     * Used for presenting the correct label to the end-user.
     *
     * @return string|null The postal code type, or null if the postal code
     *                     field isn't used.
     */
    public function getPostalCodeType();

    /**
     * Sets the postal code type.
     *
     * @param string $postalCodeType The postal code type.
     */
    public function setPostalCodeType($postalCodeType);

    /**
     * Gets the postal code pattern.
     *
     * This is a regular expression pattern used to validate postal codes.
     * Ignored if a subdivision defines its own full postal code pattern
     * (E.g. CN-91, which is Hong Kong when specified as a Chinese province).
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
     * The prefix is optional and added to postal codes only when formatting
     * an address for international mailing, as recommended by postal services.
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
