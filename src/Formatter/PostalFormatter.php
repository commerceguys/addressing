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
     * The address is first formatted without the country code, according to
     * the destination country format.
     * If the parcel is being sent to another country (origin country code
     * doesn't match the address country code), the country name is appended
     * in the origin locale (so that the local post office can understand it).
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

        $subdivisions = array(
            'administrative_area' => $address->getAdministrativeArea(),
            'locality' => $address->getLocality(),
            'dependent_locality' => $address->getDependentLocality(),
        );
        // Replace the subdivision values with the codes of any predefined ones.
        foreach ($subdivisions as $type => $id) {
            if (empty($id)) {
                // This level is empty, so there can be no sublevels.
                break;
            }
            $subdivision = $this->dataProvider->getSubdivision($id);
            if (!$subdivision) {
                // This level has no predefined subdivison, stop.
                break;
            }

            $subdivisions[$type] = $subdivision->getCode();
            if (!$subdivision->hasChildren()) {
                // The current subdivision has no children, stop.
                break;
            }
        }

        $streetAddress = $address->getAddressLine1() . "\n" . $address->getAddressLine2();
        $format = $addressFormat->getFormat();
        $replacements = array(
            '%' . AddressFormat::FIELD_ADMINISTRATIVE_AREA => $subdivisions['administrative_area'],
            '%' . AddressFormat::FIELD_LOCALITY => $subdivisions['locality'],
            '%' . AddressFormat::FIELD_DEPENDENT_LOCALITY => $subdivisions['dependent_locality'],
            '%' . AddressFormat::FIELD_POSTAL_CODE => $address->getPostalCode(),
            '%' . AddressFormat::FIELD_SORTING_CODE => $address->getSortingCode(),
            '%' . AddressFormat::FIELD_ADDRESS => $streetAddress,
            '%' . AddressFormat::FIELD_ORGANIZATION => $address->getOrganization(),
            '%' . AddressFormat::FIELD_RECIPIENT => $address->getRecipient(),
        );
        // Uppercase fields that require it.
        $uppercaseFields = $addressFormat->getUppercaseFields();
        foreach ($uppercaseFields as $uppercaseField) {
            if (isset($replacements['%' . $uppercaseField])) {
                $replacements['%' . $uppercaseField] = mb_strtoupper($replacements['%' . $uppercaseField], 'utf-8');
            }
        }
        $formattedAddress = strtr($format, $replacements);
        $formattedAddress = $this->cleanupFormattedAddress($formattedAddress);

        // Add the uppercase country name in the origin locale (to ensure
        // it's understood by the post office in the origin country).
        $destinationCountryCode = $address->getCountryCode();
        if ($destinationCountryCode != $originCountryCode) {
            $country = $this->dataProvider->getCountryName($destinationCountryCode, $originLocale);
            $formattedAddress .= "\n" . mb_strtoupper($country, 'utf-8');
        }

        return $formattedAddress;
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
