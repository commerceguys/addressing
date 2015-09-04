<?php

namespace CommerceGuys\Addressing\Model;

/**
 * Interface for immutable postal addresses.
 *
 * Extends the base interface with PSR-7 style with* mutators.
 * Each returns a new instance with the changed state.
 */
interface ImmutableAddressInterface extends AddressInterface
{
    /**
     * Returns an instance with the specified two-letter country code.
     *
     * @param string $countryCode The two-letter country code.
     *
     * @return self
     */
    public function withCountryCode($countryCode);

    /**
     * Returns an instance with the specified administrative area.
     *
     * @param string $administrativeArea The administrative area.
     */
    public function withAdministrativeArea($administrativeArea);

    /**
     * Returns an instance with the specified locality.
     *
     * @param string $locality The locality.
     *
     * @return self
     */
    public function withLocality($locality);

    /**
     * Returns an instance with the specified dependent locality.
     *
     * @param string $dependentLocality The dependent locality.
     *
     * @return self
     */
    public function withDependentLocality($dependentLocality);

    /**
     * Returns an instance with the specified postal code.
     *
     * @param string $postalCode The postal code.
     *
     * @return self
     */
    public function withPostalCode($postalCode);

    /**
     * Returns an instance with the specified sorting code.
     *
     * @param string $sortingCode The sorting code.
     *
     * @return self
     */
    public function withSortingCode($sortingCode);

    /**
     * Returns an instance with the specified first line of address block.
     *
     * @param string $addressLine1 The first line of the address block.
     *
     * @return self
     */
    public function withAddressLine1($addressLine1);

    /**
     * Returns an instance with the specified second line of address block.
     *
     * @param string $addressLine2 The second line of the address block.
     *
     * @return self
     */
    public function withAddressLine2($addressLine2);

    /**
     * Returns an instance with the specified recipient.
     *
     * @param string $recipient The recipient.
     *
     * @return self
     */
    public function withRecipient($recipient);

    /**
     * Returns an instance with the specified organization.
     *
     * @param string $organization The organization.
     *
     * @return self
     */
    public function withOrganization($organization);

    /**
     * Returns an instance with the specified locale.
     *
     * @param string $locale The locale.
     *
     * @return self
     */
    public function withLocale($locale);
}
