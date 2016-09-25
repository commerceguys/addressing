<?php

namespace CommerceGuys\Addressing\AddressFormat;

/**
 * Address format repository interface.
 */
interface AddressFormatRepositoryInterface
{
    /**
     * Returns an address format instance matching the provided country code.
     *
     * @param string $countryCode The country code.
     *
     * @return AddressFormat The address format instance.
     *
     * @throws \InvalidArgumentException
     */
    public function get($countryCode);

    /**
     * Returns all address format instances.
     *
     * @return AddressFormat[] An array of address format instances.
     */
    public function getAll();
}
