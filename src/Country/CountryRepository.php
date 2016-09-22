<?php

namespace CommerceGuys\Addressing\Country;

use CommerceGuys\Addressing\LocaleHelper;

/**
 * Provides the country list, sourced from commerceguys/intl or symfony/intl.
 *
 * Choosing the source at runtime allows integrations (such as the symfony
 * bundle) to stay agnostic about the intl library they need.
 */
class CountryRepository implements CountryRepositoryInterface
{
    /**
     * The country repository, if commerceguys/intl is used.
     *
     * @var \CommerceGuys\Intl\Country\CountryRepository
     */
    protected $countryRepository;

    /**
     * The region bundle, if symfony/intl is used.
     *
     * @var \Symfony\Component\Intl\ResourceBundle\RegionBundle
     */
    protected $regionBundle;

    /**
     * Creates a CountryRepository instance.
     */
    public function __construct()
    {
        if (class_exists('\CommerceGuys\Intl\Country\CountryRepository')) {
            $this->countryRepository = new \CommerceGuys\Intl\Country\CountryRepository();
        } elseif (class_exists('\Symfony\Component\Intl\Intl')) {
            $this->regionBundle = \Symfony\Component\Intl\Intl::getRegionBundle();
        } else {
            throw new \RuntimeException('No source of country data found: symfony/intl or commerceguys/intl must be installed.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getList($locale = null)
    {
        if ($this->countryRepository) {
            $countryNames = $this->countryRepository->getList($locale);
        } else {
            $locale = LocaleHelper::canonicalize($locale);
            // symfony/intl uses underscores.
            $locale = str_replace('-', '_', $locale);
            $countryNames = $this->regionBundle->getCountryNames($locale);
        }

        return $countryNames;
    }
}
