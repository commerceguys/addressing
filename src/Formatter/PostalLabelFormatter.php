<?php

namespace CommerceGuys\Addressing\Formatter;

use CommerceGuys\Addressing\AddressInterface;
use CommerceGuys\Addressing\AddressFormat\AddressField;
use CommerceGuys\Addressing\AddressFormat\AddressFormat;
use CommerceGuys\Addressing\AddressFormat\AddressFormatRepositoryInterface;
use CommerceGuys\Addressing\Country\CountryRepositoryInterface;
use CommerceGuys\Addressing\Subdivision\SubdivisionRepositoryInterface;

/**
 * Formats an address for a postal/shipping label.
 *
 * Takes care of uppercasing fields where required by the format (to facilitate
 * automated mail sorting).
 *
 * Requires specifying the origin country code, allowing it
 * to differentiate between domestic and international mail.
 * In case of domestic mail, the country name is not displayed at all.
 * In case of international mail:
 * 1. The postal code is prefixed with the destination's postal code prefix.
 * 2. The country name is added to the formatted address, in both the
 *    current locale and English. This matches the recommendation given by
 *    the Universal Postal Union, to avoid difficulties in countries of transit.
 */
class PostalLabelFormatter extends DefaultFormatter implements PostalLabelFormatterInterface
{
    /**
     * The default options.
     *
     * @var array
     */
    protected $defaultOptions = [
        'locale' => 'en',
        'html' => false,
        'html_tag' => 'p',
        'html_attributes' => ['translate' => 'no'],
        'origin_country' => '',
    ];

    /**
     * Creates a PostalFormatter instance.
     *
     * @param AddressFormatRepositoryInterface $addressFormatRepository
     * @param CountryRepositoryInterface       $countryRepository
     * @param SubdivisionRepositoryInterface   $subdivisionRepository
     * @param array                            $defaultOptions
     */
    public function __construct(AddressFormatRepositoryInterface $addressFormatRepository, CountryRepositoryInterface $countryRepository, SubdivisionRepositoryInterface $subdivisionRepository, array $defaultOptions = [])
    {
        parent::__construct($addressFormatRepository, $countryRepository, $subdivisionRepository, $defaultOptions);

        if (!function_exists('mb_strtoupper')) {
            throw new \RuntimeException('The "mbstring" extension is required by this class.');
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function buildView(AddressInterface $address, AddressFormat $addressFormat, array $options)
    {
        if (empty($options['origin_country'])) {
            throw new \InvalidArgumentException("The origin_country option can't be empty.");
        }

        $view = parent::buildView($address, $addressFormat, $options);
        // Uppercase fields where required by the format.
        $uppercaseFields = $addressFormat->getUppercaseFields();
        foreach ($uppercaseFields as $uppercaseField) {
            if (isset($view[$uppercaseField])) {
                $view[$uppercaseField]['value'] = mb_strtoupper($view[$uppercaseField]['value'], 'utf-8');
            }
        }
        // Handle international mailing.
        if ($address->getCountryCode() != $options['origin_country']) {
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
