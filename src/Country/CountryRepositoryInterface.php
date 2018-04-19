<?php

namespace CommerceGuys\Addressing\Country;

/**
 * Country repository interface.
 */
interface CountryRepositoryInterface
{
    /**
     * Gets a country matching the provided country code.
     *
     * @param string $countryCode The country code.
     * @param string $locale      The locale (i.e. fr-FR).
     *
     * @return Country
     */
    public function get($countryCode, $locale = null);

    /**
     * Gets all countries.
     *
     * @param string $locale The locale (i.e. fr-FR).
     *
     * @return Country[] An array of countries, keyed by country code.
     */
    public function getAll($locale = null);

    /**
     * Gets a list of countries.
     *
     * @param string $locale The locale (i.e. fr-FR).
     *
     * @return string[] An array of country names, keyed by country code.
     */
    public function getList($locale = null);
}
