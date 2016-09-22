<?php

namespace CommerceGuys\Addressing\Country;

/**
 * Country repository interface.
 */
interface CountryRepositoryInterface
{
    /**
     * Returns a list of countries.
     *
     * @param string $locale The locale (e.g. fr-FR).
     *
     * @return array An array of country names, keyed by country code.
     */
    public function getList($locale = null);
}
