<?php

namespace CommerceGuys\Addressing\Formatter;

use CommerceGuys\Addressing\Enum\AddressField;
use CommerceGuys\Addressing\Model\AddressInterface;
use CommerceGuys\Addressing\Model\AddressFormatInterface;
use CommerceGuys\Addressing\Repository\AddressFormatRepositoryInterface;
use CommerceGuys\Addressing\Repository\CountryRepositoryInterface;
use CommerceGuys\Addressing\Repository\SubdivisionRepositoryInterface;

/**
 * Formats an address for a postal/shipping label.
 *
 * Takes care of uppercasing fields where required by the format (to faciliate
 * automated mail sorting).
 *
 * Requires specifying the origin country code, allowing it
 * to differentiate between domestic and international mail.
 * In case of domestic mail, the country name is not displayed at all.
 * In case of international mail:
 * 1. The postal code is prefixed with the destination's postal code prefix.
 * 2. The country name is added to the formatted address, in both the
 *    current locale and English. This matches the recommandation given by
 *    the Universal Postal Union, to avoid difficulties in countries of transit.
 */
class PostalLabelFormatter extends DefaultFormatter implements PostalLabelFormatterInterface
{
    /**
     * The origin country code.
     *
     * @var string
     */
    protected $originCountryCode;

    /**
     * Creates a PostalFormatter instance.
     *
     * @param AddressFormatRepositoryInterface $addressFormatRepository
     * @param CountryRepositoryInterface       $countryRepository
     * @param SubdivisionRepositoryInterface   $subdivisionRepository
     * @param string                           $originCountryCode
     * @param string                           $locale
     * @param array                            $options
     */
    public function __construct(AddressFormatRepositoryInterface $addressFormatRepository, CountryRepositoryInterface $countryRepository, SubdivisionRepositoryInterface $subdivisionRepository, $originCountryCode = null, $locale = null, array $options = [])
    {
        if (!function_exists('mb_strtoupper')) {
            throw new \RuntimeException('The "mbstring" extension is required by this class.');
        }

        $this->originCountryCode = $originCountryCode;
        parent::__construct($addressFormatRepository, $countryRepository, $subdivisionRepository, $locale, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function getOriginCountryCode()
    {
        return $this->originCountryCode;
    }

    /**
     * {@inheritdoc}
     */
    public function setOriginCountryCode($originCountryCode)
    {
        $this->originCountryCode = $originCountryCode;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultOptions()
    {
        return ['html' => false] + parent::getDefaultOptions();
    }

    /**
     * {@inheritdoc}
     */
    public function format(AddressInterface $address)
    {
        if (empty($this->originCountryCode)) {
            throw new \RuntimeException("The originCountryCode can't be null.");
        }

        return parent::format($address);
    }

    /**
     * {@inheritdoc}
     */
    protected function buildView(AddressInterface $address, AddressFormatInterface $addressFormat)
    {
        $view = parent::buildView($address, $addressFormat);

        // Uppercase fields where required by the format.
        $uppercaseFields = $addressFormat->getUppercaseFields();
        foreach ($uppercaseFields as $uppercaseField) {
            if (isset($view[$uppercaseField])) {
                $view[$uppercaseField]['value'] = mb_strtoupper($view[$uppercaseField]['value'], 'utf-8');
            }
        }
        // Handle international mailing.
        if ($address->getCountryCode() != $this->originCountryCode) {
            // Prefix the postal code.
            $field = AddressField::POSTAL_CODE;
            if (isset($view[$field])) {
                $view[$field]['value'] = $addressFormat->getPostalCodePrefix() . $view[$field]['value'];
            }

            // Universal Postal Union says: "The name of the country of
            // destination shall be written preferably in the language of the
            // country of origin. To avoid any difficulty in the countries of
            // transit, it is desirable for the name of the country of
            // destination to be added in an internationally known language.
            $country = $view['country']['value'];
            $englishCountries = $this->countryRepository->getList('en');
            $englishCountry = $englishCountries[$address->getCountryCode()];
            if ($country != $englishCountry) {
                $country .= ' - ' . $englishCountry;
            }
            $view['country']['value'] = mb_strtoupper($country, 'utf-8');
        } else {
            // The country is not written in case of domestic mailing.
            $view['country']['value'] = '';
        }

        return $view;
    }
}
