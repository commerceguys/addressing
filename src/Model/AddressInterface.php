<?php

namespace CommerceGuys\Addressing\Model;

/**
 * Interface for international postal addresses.
 *
 * Field names follow the OASIS "eXtensible Address Language" (xAL) standard:
 * http://www.oasis-open.org/committees/ciq/download.shtml
 *
 * Doesn't include the sub-administrative area (United States: county,
 * Italy: province, Great Britain: county) because it is not required for
 * addressing purposes.
 */
interface AddressInterface
{
    /**
     * Gets the locale.
     *
     * Allows the initially-selected address format / subdivision translations
     * to be selected and used the next time this address is modified.
     *
     * @return string The locale.
     */
    public function getLocale();

    /**
     * Sets the locale.
     *
     * @param string $locale The locale.
     */
    public function setLocale($locale);

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
     * Gets the top-level administrative subdivision of the country.
     *
     * Called the "state" in the United States, "province" in France and Italy,
     * "county" in Great Britain, "prefecture" in Japan, etc.
     *
     * @return string The administrative area, or the subdivision id if there
     *                are predefined subdivision at this level.
     */
    public function getAdministrativeArea();

    /**
     * Sets the top-level administrative subdivision of the country.
     *
     * @param string $administrativeArea The administrative area.
     */
    public function setAdministrativeArea($administrativeArea);

    /**
     * Gets the locality (i.e city).
     *
     * Some countries do not use this field; their address lines are sufficient
     * to locate an address within a sub-administrative area.
     *
     * @return string The locality, or the subdivision id if if there are
     *                predefined subdivisions at this level
     */
    public function getLocality();

    /**
     * Sets the locality (i.e city).
     *
     * @param string $locality The locality.
     */
    public function setLocality($locality);

    /**
     * Gets the dependent locality (i.e neighbourhood).
     *
     * When representing a double-dependent locality in Great Britain, includes
     * both the double-dependent locality and the dependent locality,
     * e.g. "Whaley, Langwith".
     *
     * @return string The dependent locality, or the subdivision id if if there
     *                are predefined subdivisions at this level
     */
    public function getDependentLocality();

    /**
     * Sets the dependent locality (i.e neighbourhood).
     *
     * @param string $dependentLocality The dependent locality.
     */
    public function setDependentLocality($dependentLocality);

    /**
     * Gets the postal code.
     *
     * The value is often alphanumeric.
     *
     * @return string The postal code.
     */
    public function getPostalCode();

    /**
     * Sets the postal code of the address.
     *
     * @param string $postalCode The postal code.
     */
    public function setPostalCode($postalCode);

    /**
     * Gets the sorting code.
     *
     * For example, CEDEX in France.
     *
     * @return string The sorting code.
     */
    public function getSortingCode();

    /**
     * Sets the sorting code.
     *
     * @param string $sortingCode The sorting code.
     */
    public function setSortingCode($sortingCode);

    /**
     * Gets the first line of address block.
     *
     * @return string The first line of the address block.
     */
    public function getAddressLine1();

    /**
     * Sets the first line of address block.
     *
     * @param string $addressLine1 The first line of the address block.
     */
    public function setAddressLine1($addressLine1);

    /**
     * Gets the second line of address block.
     *
     * @return string The second line of the address block.
     */
    public function getAddressLine2();

    /**
     * Sets the second line of address block.
     *
     * @param string $addressLine2 The second line of the address block.
     */
    public function setAddressLine2($addressLine2);

    /**
     * Gets the recipient.
     *
     * @return string The recipient.
     */
    public function getRecipient();

    /**
     * Sets the recipient.
     *
     * @param string $recipient The recipient.
     */
    public function setRecipient($recipient);

    /**
     * Gets the organization.
     *
     * @return string The organization.
     */
    public function getOrganization();

    /**
     * Sets the organization.
     *
     * @param string $organization The organization.
     */
    public function setOrganization($organization);
}
