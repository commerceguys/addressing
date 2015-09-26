<?php

namespace CommerceGuys\Addressing\Model;

/**
 * Interface for address formats.
 *
 * Provides metadata for storing and presenting a country's addresses.
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
     * Gets the list of used fields.
     *
     * @return array An array of address fields.
     */
    public function getUsedFields();

    /**
     * Gets the list of used subdivision fields.
     *
     * @return array An array of address fields.
     */
    public function getUsedSubdivisionFields();

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
     * Gets the list of fields that need to be uppercased.
     *
     * @return array An array of address fields.
     */
    public function getUppercaseFields();

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
     * Gets the locality type.
     *
     * Used for presenting the correct label to the end-user.
     *
     * @return string|null The locality type, or null if the locality field
     *                     isn't used.
     */
    public function getLocalityType();

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
     * Gets the postal code type.
     *
     * Used for presenting the correct label to the end-user.
     *
     * @return string|null The postal code type, or null if the postal code
     *                     field isn't used.
     */
    public function getPostalCodeType();

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
     * Gets the postal code prefix.
     *
     * The prefix is optional and added to postal codes only when formatting
     * an address for international mailing, as recommended by postal services.
     *
     * @return string|null The postal code prefix.
     */
    public function getPostalCodePrefix();
}
