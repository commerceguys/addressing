<?php

namespace CommerceGuys\Addressing;

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
     */
    public function withCountryCode(string $countryCode): ImmutableAddressInterface;

    /**
     * Returns an instance with the specified administrative area.
     */
    public function withAdministrativeArea(string $administrativeArea);

    /**
     * Returns an instance with the specified locality.
     */
    public function withLocality(string $locality): ImmutableAddressInterface;

    /**
     * Returns an instance with the specified dependent locality.
     *
     */
    public function withDependentLocality(string $dependentLocality): ImmutableAddressInterface;

    /**
     * Returns an instance with the specified postal code.
     */
    public function withPostalCode(string $postalCode): ImmutableAddressInterface;

    /**
     * Returns an instance with the specified sorting code.
     */
    public function withSortingCode(string $sortingCode): ImmutableAddressInterface;

    /**
     * Returns an instance with the specified first line of address block.
     */
    public function withAddressLine1(string $addressLine1): ImmutableAddressInterface;

    /**
     * Returns an instance with the specified second line of address block.
     */
    public function withAddressLine2(string $addressLine2): ImmutableAddressInterface;

    /**
     * Returns an instance with the specified third line of address block.
     */
    public function withAddressLine3(string $addressLine3): ImmutableAddressInterface;

    /**
     * Returns an instance with the specified organization.
     */
    public function withOrganization(string $organization): ImmutableAddressInterface;

    /**
     * Returns an instance with the specified given name.
     */
    public function withGivenName(string $givenName): ImmutableAddressInterface;

    /**
     * Returns an instance with the specified additional name.
     */
    public function withAdditionalName(string $additionalName): ImmutableAddressInterface;

    /**
     * Returns an instance with the specified family name.
     */
    public function withFamilyName(string $familyName): ImmutableAddressInterface;

    /**
     * Returns an instance with the specified locale.
     */
    public function withLocale(string $locale): ImmutableAddressInterface;
}
