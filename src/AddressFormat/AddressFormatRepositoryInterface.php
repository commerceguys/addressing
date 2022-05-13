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
     * @throws \InvalidArgumentException
     */
    public function get(string $countryCode): AddressFormat;

    /**
     * Returns all address format instances.
     *
     * @return AddressFormat[] An array of address format instances.
     */
    public function getAll(): array;
}
