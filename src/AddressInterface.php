<?php

namespace CommerceGuys\Address;

interface AddressInterface
{
    const COUNTRY = "country";

    const ADMINISTRATIVE_AREA = "administrative_area"

    const LOCALITY = "locality";

    const DEPENDENT_LOCALITY = "dependent_locality";

    const POSTAL_CODE = "postal_code"

    const SORTING_CODE = "sorting_code"

    const ADDRESS_LINE_1 = "address_line_1"

    const ADDRESS_LINE_2 = "address_line_2"

    const ADDRESS_LINE_3 = "address_line_3"

    const ORGANIZATION = "organization";

    const RECIPIENT = "recipient"

    /**
     * Returns the postal country code of the address.
     *
     * This is generally a CLDR country code, i.e. "US", "GB" or "FR".
     *
     * @return string The country code.
     */
    public function getPostalCountryCode();

    /**
     * Returns the top-level administrative subdivision of the country.
     *
     * @return string The administrative area code
     */
    public function getAdministrativeArea();

    /**
     * Returns the locality of the address (i.e. the city).
     *
     * @return string The locality of the address
     */
    public function getLocality();

    /**
     * Returns the dependent locality of the address (i.e. the neighborhood).
     *
     * @return string The dependent locality of the address
     */
    public function getDependentLocality();

    /**
     * Returns the postal code of the address.
     *
     * @return string The postal code
     */
    public function getPostalCode();

    /**
     * Returns the postal sorting code of the address.
     *
     * @return string The postal sorting code
     */
    public function getSortingCode();

    /**
     * Returns the first line of address block.
     *
     * @return string The first line of the address block
     */
    public function getAddressLine1();

    /**
     * Returns the second line of address block.
     *
     * @return string The second line of the address block
     */
    public function getAddressLine2();

    /**
     * Returns the third line of address block.
     *
     * @return string The third line of the address block
     */
    public function getAddressLine3();

    /**
     * Returns the organization of the address.
     *
     * @return string The organization of the address
     */
    public function getOrganization();

    /**
     * Returns the recipient of the address.
     *
     * @return string The recipient of the address
     */
    public function getRecipient();
}
