<?php

namespace CommerceGuys\Addressing\Formatter;

use CommerceGuys\Addressing\AddressInterface;
use CommerceGuys\Addressing\Metadata\AddressMetadataRepositoryInterface;
use CommerceGuys\Addressing\Metadata\AddressFormat;

class PostalFormatter
{
    /**
     * The metadata repository.
     *
     * @var AddressMetadataRepositoryInterface
     */
    protected $repository;

    /**
     * Creates a PostalFormatter instance.
     *
     * @param AddressMetadataRepositoryInterface $repository The metadata repository.
     */
    public function __construct(AddressMetadataRepositoryInterface $repository)
    {
        $this->repository = $repository;
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
     *                                            i.e US if the parcels are sent from the USA.
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
        $addressFormat = $this->repository->getAddressFormat($countryCode, $originLocale);

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
            $subdivision = $this->repository->getSubdivision($id);
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
                $replacements['%' . $uppercaseField] = $this->uppercaseString($replacements['%' . $uppercaseField]);
            }
        }
        $formattedAddress = strtr($format, $replacements);
        // Remove empty lines.
        $addressLines = explode("\n", $formattedAddress);
        $addressLines = array_filter($addressLines);
        $formattedAddress = implode("\n", $addressLines);

        // Add the uppercase country name in the origin locale (to ensure
        // it's understood by the post office in the origin country).
        $destinationCountryCode = $address->getCountryCode();
        if ($destinationCountryCode != $originCountryCode) {
            $country = $this->repository->getCountryName($destinationCountryCode, $originLocale);
            $formattedAddress .= "\n" . $this->uppercaseString($country);
        }

        return $formattedAddress;
    }

    /**
     * Converts a UTF-8 string to uppercase.
     *
     * @param string $string The string to uppercase.
     *
     * @return string The uppercased string.
     */
    protected function uppercaseString($string)
    {
        if (function_exists('mb_strtoupper')) {
            return mb_strtoupper($string, 'utf-8');
        }

        // Uppercase ASCII and latin-1 accented characters.
        setlocale(LC_ALL, 'C');
        $string = strtoupper($string);
        $string = preg_replace_callback('/\xC3[\xA0-\xB6\xB8-\xBE]/', function ($matches) {
            return $matches[0][0] . chr(ord($matches[0][1]) ^ 32);
        }, $string);

        return $string;
    }
}
