<?php

namespace CommerceGuys\Addressing\Metadata;

/**
 * Address metadata repository interface.
 */
interface AddressMetadataRepositoryInterface
{
    /**
     * Returns the localized country name matching the provided country code.
     *
     * @param string $countryCode The country code.
     * @param string $locale      The locale (i.e. fr-FR).
     *
     * @return string The country name.
     */
    public function getCountryName($countryCode, $locale = null);

    /**
     * Returns a list of all country names.
     *
     * @param string $locale The locale (i.e. fr-FR).
     *
     * @return array The country names, keyed by country code.
     */
    public function getCountryNames($locale = null);

    /**
     * Returns an address format instance matching the provided country code.
     *
     * If no matching address format was found, the fallback address format (ZZ)
     * is returned instead.
     *
     * @param string $countryCode The country code.
     * @param string $locale      The locale (i.e. fr-FR).
     *
     * @return AddressFormat The address format instance.
     */
    public function getAddressFormat($countryCode, $locale = null);

    /**
     * Returns a subdivision instance matching the provided id.
     *
     * @param string $id     The subdivision id.
     * @param string $locale The locale (i.e. fr-FR).
     *
     * @return Subdivision|null The subdivision instance, if found.
     */
    public function getSubdivision($id, $locale = null);

    /**
     * Returns all available subdivision instances for the provided country code.
     *
     * @param string  $countryCode The country code.
     * @param integer $parentId    The parent id.
     * @param string  $locale      The locale (i.e. fr-FR).
     *
     * @return Subdivision[] An array of subdivision instances.
     */
    public function getSubdivisions($countryCode, $parentId = 0, $locale = null);
}
