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
     *
     * @return AddressFormat The address format instance.
     */
    public function get($countryCode);

    /**
     * Returns all address format instances.
     *
     * @return AddressFormat[] An array of address format instances.
     */
    public function getAll();
}
