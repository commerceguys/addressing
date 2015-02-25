<?php

namespace CommerceGuys\Addressing\Formatter;

use CommerceGuys\Addressing\Model\AddressInterface;
use CommerceGuys\Addressing\Model\AddressFormat;
use CommerceGuys\Addressing\Provider\DataProviderInterface;

class PostalFormatter
{
    /**
     * The data provider.
     *
     * @var DataProviderInterface
     */
    protected $dataProvider;

    /**
     * Creates a PostalFormatter instance.
     *
     * @param DataProviderInterface $dataProvider The data provider.
     */
    public function __construct(DataProviderInterface $dataProvider)
    {
        if (!function_exists('mb_strtoupper')) {
            throw new \RuntimeException('The "mbstring" extension is required by this class.');
        }

        $this->dataProvider = $dataProvider;
    }

    /**
     * Formats an address for postal purposes.
     *
     * The address is formatted without the country code, according to the
     * destination country format.
     * If the parcel is being sent to another country:
     * 1. The postal code is prefixed with the destination's postal code prefix.
     * 2. The country name is appended to the formatted address in the origin
     *    locale (so that the local post office can understand it).
     *
     * @param AddressInterface $address           The address.
     * @param string           $originCountryCode The country code of the origin country.
     *                                            e.g. US if the parcels are sent from the USA.
     * @param string           $originLocale      The locale used to get the country names.
     *
     * @return string The formatted address, divided by unix newlines (\n).
     */
    public function format(AddressInterface $address, $originCountryCode, $originLocale = 'en')
    {
        $countryCode = $address->getCountryCode();
        // Fetching the address format in the origin locale results in the
        // minor-to-major format being used for China/Japan/Korea in case of
        // international shippments, increasing the chances of the
        // address being interpreted correctly.
        $addressFormat = $this->dataProvider->getAddressFormat($countryCode, $originLocale);

        $replacements = $this->getSubdivisionReplacements($address);
        $replacements += [
            '%' . AddressFormat::FIELD_POSTAL_CODE => $address->getPostalCode(),
            '%' . AddressFormat::FIELD_SORTING_CODE => $address->getSortingCode(),
            '%' . AddressFormat::FIELD_ADDRESS => $address->getAddressLine1() . "\n" . $address->getAddressLine2(),
            '%' . AddressFormat::FIELD_ORGANIZATION => $address->getOrganization(),
            '%' . AddressFormat::FIELD_RECIPIENT => $address->getRecipient(),
        ];
        // Prefix the postal code for international mailing.
        if ($countryCode != $originCountryCode) {
            $token = '%' . AddressFormat::FIELD_POSTAL_CODE;
            $replacements[$token] = $addressFormat->getPostalCodePrefix() . $replacements[$token];
        }
        // Uppercase fields that require it.
        $uppercaseFields = $addressFormat->getUppercaseFields();
        foreach ($uppercaseFields as $uppercaseField) {
            if (isset($replacements['%' . $uppercaseField])) {
                $replacements['%' . $uppercaseField] = mb_strtoupper($replacements['%' . $uppercaseField], 'utf-8');
            }
        }
        $format = $addressFormat->getFormat();
        $formattedAddress = strtr($format, $replacements);
        $formattedAddress = $this->cleanupFormattedAddress($formattedAddress);

        // Add the uppercase country name in the origin locale (to ensure
        // it's understood by the post office in the origin country).
        if ($countryCode != $originCountryCode) {
            $country = $this->dataProvider->getCountryName($countryCode, $originLocale);
            $formattedAddress .= "\n" . mb_strtoupper($country, 'utf-8');
        }

        return $formattedAddress;
    }

    /**
     * Gets the replacements for subdivision field tokens.
     *
     * If the address value maps to a predefined subdivision, the subdivision
     * code is used as a replacement. Otherwise, the original value is used.
     *
     * @param AddressInterface $address The address.
     *
     * @return array The replacements array keyed by token.
     */
    protected function getSubdivisionReplacements(AddressInterface $address)
    {
        $replacements = [
            '%' . AddressFormat::FIELD_ADMINISTRATIVE_AREA => $address->getAdministrativeArea(),
            '%' . AddressFormat::FIELD_LOCALITY => $address->getLocality(),
            '%' . AddressFormat::FIELD_DEPENDENT_LOCALITY => $address->getDependentLocality(),
        ];
        // Replace the subdivision values with the codes of any predefined ones.
        foreach ($replacements as $key => $id) {
            if (empty($id)) {
                // This level is empty, so there can be no sublevels.
                break;
            }
            $subdivision = $this->dataProvider->getSubdivision($id);
            if (!$subdivision) {
                // This level has no predefined subdivisions, stop.
                break;
            }

            $replacements[$key] = $subdivision->getCode();
            if (!$subdivision->hasChildren()) {
                // The current subdivision has no children, stop.
                break;
            }
        }

        return $replacements;
    }

    /**
     * Removes empty lines, leading punctuation, excess whitespace.
     *
     * @param string $formattedAddress The formatted address.
     *
     * @return string The cleaned up formatted address.
     */
    protected function cleanupFormattedAddress($formattedAddress)
    {
        $addressLines = explode("\n", $formattedAddress);
        foreach ($addressLines as $index => $line) {
            // Remove any leading punctuation added because of missing data.
            $addressLines[$index] = trim(preg_replace('/^[-,\\s]+/', ' ', $line));
        }
        // Remove empty lines.
        $addressLines = array_filter($addressLines);

        return implode("\n", $addressLines);
    }
}
