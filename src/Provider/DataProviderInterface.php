<?php

namespace CommerceGuys\Addressing\Provider;

use CommerceGuys\Addressing\Model\AddressFormat;
use CommerceGuys\Addressing\Model\Subdivision;

/**
 * Data provider interface.
 *
 * Acts as a facade in front of country/subdivision/address_format repositories,
 * and serves as the single point of contact between the data layer and the
 * rest of the library. This allows external systems to integrate custom storage
 * through a single class (e.g. DoctrineDataProvider or DrupalDataProvider,
 * loading data stored in entities).
 */
interface DataProviderInterface
{
    /**
     * Returns the localized country name matching the provided country code.
     *
     * @param string $countryCode The country code.
     * @param string $locale      The locale (e.g. fr-FR).
     *
     * @return string The country name.
     */
    public function getCountryName($countryCode, $locale = null);

    /**
     * Returns a list of all country names.
     *
     * @param string $locale The locale (e.g. fr-FR).
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
     * @param string $locale      The locale (e.g. fr-FR).
     *
     * @return AddressFormat The address format instance.
     */
    public function getAddressFormat($countryCode, $locale = null);

    /**
     * Returns all available address format instances.
     *
     * @param string $locale The locale (e.g. fr-FR).
     *
     * @return AddressFormat[] An array of address format instances.
     */
    public function getAddressFormats($locale = null);

    /**
     * Returns a subdivision instance matching the provided id.
     *
     * @param string $id     The subdivision id.
     * @param string $locale The locale (e.g. fr-FR).
     *
     * @return Subdivision|null The subdivision instance, if found.
     */
    public function getSubdivision($id, $locale = null);

    /**
     * Returns all available subdivision instances for the provided country code.
     *
     * @param string $countryCode The country code.
     * @param int    $parentId    The parent id.
     * @param string $locale      The locale (e.g. fr-FR).
     *
     * @return Subdivision[] An array of subdivision instances.
     */
    public function getSubdivisions($countryCode, $parentId = null, $locale = null);
}
