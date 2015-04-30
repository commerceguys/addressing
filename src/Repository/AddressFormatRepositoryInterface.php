<?php

namespace CommerceGuys\Addressing\Repository;

use CommerceGuys\Addressing\Model\AddressFormat;

/**
 * Address format repository interface.
 */
interface AddressFormatRepositoryInterface
{
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
    public function get($countryCode, $locale = null);

    /**
     * Returns all address format instances.
     *
     * @param string $locale The locale (e.g. fr-FR).
     *
     * @return AddressFormat[] An array of address format instances.
     */
    public function getAll($locale = null);
}
