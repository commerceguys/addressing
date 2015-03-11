<?php

namespace CommerceGuys\Addressing\Provider;

use CommerceGuys\Addressing\Repository\AddressFormatRepositoryInterface;
use CommerceGuys\Addressing\Repository\AddressFormatRepository;
use CommerceGuys\Addressing\Repository\SubdivisionRepositoryInterface;
use CommerceGuys\Addressing\Repository\SubdivisionRepository;

/**
 * @codeCoverageIgnore
 */
class DataProvider implements DataProviderInterface
{
    /**
     * The address format repository.
     *
     * @var AddressFormatRepositoryInterface
     */
    protected $addressFormatRepository;

    /**
     * The subdivision repository.
     *
     * @var SubdivisionRepositoryInterface
     */
    protected $subdivisionRepository;

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
     * Creates a DataProvider instance.
     *
     * @param AddressFormatRepositoryInterface $addressFormatRepository
     * @param SubdivisionRepositoryInterface   $subdivisionRepository
     */
    public function __construct(
        AddressFormatRepositoryInterface $addressFormatRepository = null,
        SubdivisionRepositoryInterface $subdivisionRepository = null
    ) {
        $this->addressFormatRepository = $addressFormatRepository ?: new AddressFormatRepository();
        $this->subdivisionRepository = $subdivisionRepository ?: new SubdivisionRepository();

        // Allow both commerceguys/intl and symfony/intl to be used as the
        // source of country data. To be removed once commerceguys/intl is
        // deprecated in favor of the still unreleased symfony/intl 2.7.
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
    public function getCountryName($countryCode, $locale = null)
    {
        if ($this->countryRepository) {
            $country = $this->countryRepository->get($countryCode, $locale);
            $countryName = $country->getName();
        } else {
            $locale = $this->canonicalizeLocale($locale);
            $countryName = $this->regionBundle->getCountryName($countryCode, $locale);
        }

        return $countryName;
    }

    /**
     * {@inheritdoc}
     */
    public function getCountryNames($locale = null)
    {
        if ($this->countryRepository) {
            $countries = $this->countryRepository->getAll($locale);
            $countryNames = [];
            foreach ($countries as $countryCode => $country) {
                $countryNames[$countryCode] = $country->getName();
            }
        } else {
            $locale = $this->canonicalizeLocale($locale);
            $countryNames = $this->regionBundle->getCountryNames($locale);
        }

        return $countryNames;
    }

    /**
     * Canonicalize the given locale.
     *
     * Note: commerceguys/intl does this internally, so this method only
     * needs to be invoked when using symfony/intl.
     *
     * @param string $locale The locale.
     *
     * @return string The canonicalized locale.
     */
    protected function canonicalizeLocale($locale)
    {
        if (is_null($locale)) {
            return $locale;
        }

        $locale = str_replace('-', '_', strtolower($locale));
        $localeParts = explode('_', $locale);
        foreach ($localeParts as $index => $part) {
            if ($index === 0) {
                // The language code should stay lowercase.
                continue;
            }

            if (strlen($part) == 4) {
                // Script code.
                $localeParts[$index] = ucfirst($part);
            } else {
                // Country or variant code.
                $localeParts[$index] = strtoupper($part);
            }
        }

        return implode('_', $localeParts);
    }

    /**
     * {@inheritdoc}
     */
    public function getAddressFormat($countryCode, $locale = null)
    {
        return $this->addressFormatRepository->get($countryCode, $locale);
    }

    /**
     * {@inheritdoc}
     */
    public function getAddressFormats($locale = null)
    {
        return $this->addressFormatRepository->getAll($locale);
    }

    /**
     * {@inheritdoc}
     */
    public function getSubdivision($id, $locale = null)
    {
        return $this->subdivisionRepository->get($id, $locale);
    }

    /**
     * {@inheritdoc}
     */
    public function getSubdivisions($countryCode, $parentId = null, $locale = null)
    {
        return $this->subdivisionRepository->getAll($countryCode, $parentId, $locale);
    }
}
