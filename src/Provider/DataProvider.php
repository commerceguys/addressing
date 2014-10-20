<?php

namespace CommerceGuys\Addressing\Provider;

use CommerceGuys\Intl\Country\CountryRepositoryInterface;
use CommerceGuys\Intl\Country\CountryRepository;
use CommerceGuys\Addressing\Repository\AddressFormatRepositoryInterface;
use CommerceGuys\Addressing\Repository\AddressFormatRepository;
use CommerceGuys\Addressing\Repository\SubdivisionRepositoryInterface;
use CommerceGuys\Addressing\Repository\SubdivisionRepository;

class DataProvider implements DataProviderInterface
{
    /**
     * The country repository.
     *
     * @var CountryRepositoryInterface
     */
    protected $countryRepository;

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
     * Creates a DataProvider instance.
     *
     * @param CountryRepositoryInterface       $countryRepository
     * @param AddressFormatRepositoryInterface $addressFormatRepository
     * @param SubdivisionRepositoryInterface   $subdivisionRepository
     */
    public function __construct(
        CountryRepositoryInterface $countryRepository = null,
        AddressFormatRepositoryInterface $addressFormatRepository = null,
        SubdivisionRepositoryInterface $subdivisionRepository = null)
    {
        $this->countryRepository = $countryRepository ?: new CountryRepository();
        $this->addressFormatRepository = $addressFormatRepository ?: new AddressFormatRepository();
        $this->subdivisionRepository = $subdivisionRepository ?: new SubdivisionRepository();
    }

    /**
     * {@inheritdoc}
     */
    public function getCountryName($countryCode, $locale = null)
    {
        // The CountryRepository doesn't accept a null locale.
        $locale = $locale ?: 'en';
        $country = $this->countryRepository->get($countryCode, $locale);

        return $country->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function getCountryNames($locale = null)
    {
        // The CountryRepository doesn't accept a null locale.
        $locale = $locale ?: 'en';
        $countries = $this->countryRepository->getAll($locale);
        $countryNames = array();
        foreach ($countries as $countryCode => $country) {
            $countryNames[$countryCode] = $country->getName();
        }

        return $countryNames;
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
    public function getSubdivision($id, $locale = null)
    {
        return $this->subdivisionRepository->get($id, $locale);
    }

    /**
     * {@inheritdoc}
     */
    public function getSubdivisions($countryCode, $parentId = 0, $locale = null)
    {
        return $this->subdivisionRepository->getAll($countryCode, $parentId, $locale);
    }
}
