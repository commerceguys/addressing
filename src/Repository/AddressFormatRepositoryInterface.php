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
     * @param string $locale      The locale (i.e. fr-FR).
     *
     * @return AddressFormat The address format instance.
     */
    public function get($countryCode, $locale = null);
}
