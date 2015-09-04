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
 *
 * Makes no assumptions about mutability. The implementing application
 * can extend the interface to provide setters, or implement a value object
 * that uses either PSR-7 style with* mutators or relies on an AddressBuilder.
 *
 * @see \CommerceGuys\Addressing\Model\ImmutableAddressInterface
 */
interface AddressInterface
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
     * Gets the administrative area.
     *
     * Called the "state" in the United States, "province" in France and Italy,
     * "county" in Great Britain, "prefecture" in Japan, etc.
     *
     * @return string The administrative area, or the subdivision id if there
     *                are predefined subdivision at this level.
     */
    public function getAdministrativeArea();

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
     * Gets the postal code.
     *
     * The value is often alphanumeric.
     *
     * @return string The postal code.
     */
    public function getPostalCode();

    /**
     * Gets the sorting code.
     *
     * For example, CEDEX in France.
     *
     * @return string The sorting code.
     */
    public function getSortingCode();

    /**
     * Gets the first line of address block.
     *
     * @return string The first line of the address block.
     */
    public function getAddressLine1();

    /**
     * Gets the second line of address block.
     *
     * @return string The second line of the address block.
     */
    public function getAddressLine2();

    /**
     * Gets the recipient.
     *
     * @return string The recipient.
     */
    public function getRecipient();

    /**
     * Gets the organization.
     *
     * @return string The organization.
     */
    public function getOrganization();

    /**
     * Gets the locale.
     *
     * Allows the initially-selected address format / subdivision translations
     * to be selected and used the next time this address is modified.
     *
     * @return string The locale.
     */
    public function getLocale();
}
