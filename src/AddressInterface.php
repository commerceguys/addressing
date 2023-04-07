<?php

namespace CommerceGuys\Addressing;

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
 * @see \CommerceGuys\Addressing\ImmutableAddressInterface
 */
interface AddressInterface
{
    /**
     * Gets the two-letter country code.
     *
     * This is a CLDR country code, since CLDR includes additional countries
     * for addressing purposes, such as Canary Islands (IC).
     *
     * @return ?string The two-letter country code.
     */
    public function getCountryCode(): ?string;

    /**
     * Gets the administrative area.
     *
     * Called the "state" in the United States, "region" in France, "province" in Italy,
     * "county" in Great Britain, "prefecture" in Japan, etc.
     *
     * @return ?string The administrative area. A subdivision code if there
     *                are predefined subdivision at this level.
     */
    public function getAdministrativeArea(): ?string;

    /**
     * Gets the locality (i.e city).
     *
     * Some countries do not use this field; their address lines are sufficient
     * to locate an address within a sub-administrative area.
     *
     * @return string|null The administrative area. A subdivision code if there
     *                are predefined subdivision at this level.
     */
    public function getLocality(): ?string;

    /**
     * Gets the dependent locality (i.e neighbourhood).
     *
     * When representing a double-dependent locality in Great Britain, includes
     * both the double-dependent locality and the dependent locality,
     * e.g. "Whaley, Langwith".
     *
     * @return string The administrative area. A subdivision code if there
     *                are predefined subdivision at this level.
     */
    public function getDependentLocality(): ?string;

    /**
     * Gets the postal code.
     *
     * The value is often alphanumeric.
     */
    public function getPostalCode(): ?string;

    /**
     * Gets the sorting code.
     *
     * For example, CEDEX in France.
     */
    public function getSortingCode(): ?string;

    /**
     * Gets the first line of address block.
     */
    public function getAddressLine1(): ?string;

    /**
     * Gets the second line of address block.
     */
    public function getAddressLine2(): ?string;

    /**
     * Gets the third line of address block.
     */
    public function getAddressLine3(): ?string;

    /**
     * Gets the organization.
     */
    public function getOrganization(): ?string;

    /**
     * Gets the given name (i.e first name).
     */
    public function getGivenName(): ?string;

    /**
     * Gets the additional name.
     *
     * Can be used to hold a middle name, or a patronymic.
     * If a remote API does not have an additional_name/middle_name parameter,
     * append it to the given name.
     */
    public function getAdditionalName(): ?string;

    /**
     * Gets the family name (i.e last name).
     */
    public function getFamilyName(): ?string;

    /**
     * Gets the locale.
     *
     * Allows the initially-selected address format / subdivision translations
     * to be selected and used the next time this address is modified.
     */
    public function getLocale(): ?string;
}
