<?php

namespace CommerceGuys\Addressing\Repository;

use CommerceGuys\Addressing\Model\AddressFormat;

class AddressFormatRepository implements AddressFormatRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function get($countryCode, $locale = null)
    {
        $definitions = $this->getDefinitions();
        if (!isset($definitions[$countryCode])) {
            $countryCode = 'ZZ';
        }
        $definition = [];
        $definition['country_code'] = $countryCode;
        $definition += $definitions[$countryCode];
        // ZZ holds the default properties.
        // If the definition is already ZZ, repeating this won't hurt.
        $definition += $definitions['ZZ'];
        $addressFormat = new AddressFormat($definition);

        return $addressFormat;
    }

    /**
     * {@inheritdoc}
     */
    public function getAll($locale = null)
    {
        $definitions = $this->getDefinitions();
        $addressFormats = [];
        foreach ($definitions as $countryCode => $definition) {
            $definition['country_code'] = $countryCode;
            if ($countryCode != 'ZZ') {
                // ZZ holds the default properties.
                $definition += $definitions['ZZ'];
            }
            $addressFormats[$countryCode] = new AddressFormat($definition);
        }

        return $addressFormats;
    }

    /**
     * Loads the address format definitions.
     *
     * @return array The address format definitions.
     */
    protected function getDefinitions()
    {
        // @codingStandardsIgnoreStart
        $definitions = [
            'ZZ' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality',
                ],
                'uppercase_fields' => [
                    'locality',
                ],
                'administrative_area_type' => 'province',
                'locality_type' => 'city',
                'dependent_locality_type' => 'suburb',
                'postal_code_type' => 'postal',
            ],
            'AF' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality\n%postalCode",
                'postal_code_pattern' => '\d{4}',
            ],
            'AX' => [
                'format' => "%organization\n%recipient\n%addressLine1\n%addressLine2\n%postalCode %locality\nÅLAND",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'postalCode',
                ],
                'postal_code_pattern' => '22\d{3}',
                'postal_code_prefix' => 'AX-',
            ],
            'AL' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%postalCode\n%locality",
                'postal_code_pattern' => '\d{4}',
            ],
            'DZ' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '\d{5}',
            ],
            'AS' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality %administrativeArea %postalCode",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'administrativeArea', 'postalCode',
                ],
                'uppercase_fields' => [
                    'addressLine1', 'addressLine2', 'locality', 'recipient', 'organization', 'administrativeArea',
                ],
                'administrative_area_type' => 'state',
                'postal_code_type' => 'zip',
                'postal_code_pattern' => '(96799)(?:[ \-](\d{4}))?',
            ],
            'AD' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => 'AD[1-7]0\d',
            ],
            'AI' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality\n%postalCode",
                'postal_code_pattern' => '(?:AI-)?2640',
            ],
            'AG' => [
                'required_fields' => [
                    'recipient', 'addressLine1',
                ],
            ],
            'AR' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality\n%administrativeArea",
                'uppercase_fields' => [
                    'addressLine1', 'addressLine2', 'locality', 'postalCode',
                ],
                'postal_code_pattern' => '((?:[A-HJ-NP-Z])?\d{4})([A-Z]{3})?',
            ],
            'AM' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%postalCode\n%locality\n%administrativeArea",
                'postal_code_pattern' => '(?:37)?\d{4}',
            ],
            'AC' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality\n%postalCode",
                'postal_code_pattern' => 'ASCN 1ZZ',
            ],
            'AU' => [
                'format' => "%organization\n%recipient\n%addressLine1\n%addressLine2\n%locality %administrativeArea %postalCode",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'administrativeArea', 'postalCode',
                ],
                'uppercase_fields' => [
                    'locality', 'administrativeArea',
                ],
                'administrative_area_type' => 'state',
                'locality_type' => 'suburb',
                'postal_code_pattern' => '\d{4}',
            ],
            'AT' => [
                'format' => "%organization\n%recipient\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'postalCode',
                ],
                'postal_code_pattern' => '\d{4}',
            ],
            'AZ' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '\d{4}',
                'postal_code_prefix' => 'AZ ',
            ],
            'BS' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality, %administrativeArea",
                'administrative_area_type' => 'island',
            ],
            'BH' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality %postalCode",
                'postal_code_pattern' => '(?:\d|1[0-2])\d{2}',
            ],
            'BD' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality - %postalCode",
                'postal_code_pattern' => '\d{4}',
            ],
            'BB' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality, %administrativeArea %postalCode",
                'administrative_area_type' => 'parish',
                'postal_code_pattern' => 'BB\d{5}',
            ],
            'BY' => [
                'format' => "%administrativeArea\n%postalCode %locality\n%addressLine2\n%addressLine1\n%organization\n%recipient",
                'postal_code_pattern' => '\d{6}',
            ],
            'BE' => [
                'format' => "%organization\n%recipient\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'postalCode',
                ],
                'postal_code_pattern' => '\d{4}',
            ],
            'BJ' => [
                'uppercase_fields' => [
                    'addressLine1', 'addressLine2', 'locality',
                ],
            ],
            'BM' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality %postalCode",
                'postal_code_pattern' => '[A-Z]{2} ?[A-Z0-9]{2}',
            ],
            'BT' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality %postalCode",
                'postal_code_pattern' => '\d{5}',
            ],
            'BO' => [
                'uppercase_fields' => [
                    'addressLine1', 'addressLine2', 'locality',
                ],
            ],
            'BA' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '\d{5}',
            ],
            'BR' => [
                'format' => "%organization\n%recipient\n%addressLine1\n%addressLine2\n%dependentLocality\n%locality-%administrativeArea\n%postalCode",
                'required_fields' => [
                    'recipient', 'addressLine1', 'administrativeArea', 'locality', 'postalCode',
                ],
                'uppercase_fields' => [
                    'locality', 'administrativeArea',
                ],
                'administrative_area_type' => 'state',
                'dependent_locality_type' => 'neighborhood',
                'postal_code_pattern' => '\d{5}-?\d{3}',
            ],
            'IO' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality\n%postalCode",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'postalCode',
                ],
                'uppercase_fields' => [
                    'locality', 'postalCode',
                ],
                'postal_code_pattern' => 'BBND 1ZZ',
            ],
            'VG' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality\n%postalCode",
                'required_fields' => [
                    'recipient', 'addressLine1',
                ],
                'postal_code_pattern' => 'VG\d{4}',
            ],
            'BN' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality %postalCode",
                'postal_code_pattern' => '[A-Z]{2} ?\d{4}',
            ],
            'BG' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '\d{4}',
            ],
            'BF' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality %sortingCode",
            ],
            'KH' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality %postalCode",
                'postal_code_pattern' => '\d{5}',
            ],
            'CA' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality %administrativeArea %postalCode",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'administrativeArea', 'postalCode',
                ],
                'uppercase_fields' => [
                    'addressLine1', 'addressLine2', 'locality', 'recipient', 'organization', 'administrativeArea',
                    6 => 'postalCode',
                ],
                'postal_code_pattern' => '[ABCEGHJKLMNPRSTVXY]\d[ABCEGHJ-NPRSTV-Z] ?\d[ABCEGHJ-NPRSTV-Z]\d',
            ],
            'CV' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality\n%administrativeArea",
                'administrative_area_type' => 'island',
                'postal_code_pattern' => '\d{4}',
            ],
            'KY' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%administrativeArea %postalCode",
                'required_fields' => [
                    'recipient', 'addressLine1', 'administrativeArea',
                ],
                'administrative_area_type' => 'island',
                'postal_code_pattern' => 'KY\d-\d{4}',
            ],
            'CL' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality\n%administrativeArea",
                'postal_code_pattern' => '\d{7}',
            ],
            'CN' => [
                'locale' => 'zh-Hans',
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%dependentLocality\n%locality\n%administrativeArea, %postalCode",
                'local_format' => "%postalCode\n%administrativeArea%locality%dependentLocality\n%addressLine2\n%addressLine1\n%organization\n%recipient",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'administrativeArea', 'postalCode',
                ],
                'uppercase_fields' => [
                    'administrativeArea',
                ],
                'dependent_locality_type' => 'district',
                'postal_code_pattern' => '\d{6}',
            ],
            'CX' => [
                'format' => "%organization\n%recipient\n%addressLine1\n%addressLine2\n%locality %administrativeArea %postalCode",
                'uppercase_fields' => [
                    'locality', 'administrativeArea',
                ],
                'postal_code_pattern' => '6798',
            ],
            'CC' => [
                'format' => "%organization\n%recipient\n%addressLine1\n%addressLine2\n%locality %administrativeArea %postalCode",
                'uppercase_fields' => [
                    'locality', 'administrativeArea',
                ],
                'postal_code_pattern' => '6799',
            ],
            'CO' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality, %administrativeArea, %postalCode",
                'required_fields' => [
                    'recipient', 'addressLine1', 'administrativeArea',
                ],
                'administrative_area_type' => 'department',
                'postal_code_pattern' => '\d{6}',
            ],
            'KM' => [
                'uppercase_fields' => [
                    'addressLine1', 'addressLine2', 'locality',
                ],
            ],
            'CR' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%administrativeArea, %locality\n%postalCode",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'administrativeArea',
                ],
                'postal_code_pattern' => '\d{4,5}|\d{3}-\d{4}',
            ],
            'CI' => [
                'format' => "%recipient\n%organization\n%sortingCode %addressLine1\n%addressLine2 %locality %sortingCode",
            ],
            'HR' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '\d{5}',
                'postal_code_prefix' => 'HR-',
            ],
            'CY' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '\d{4}',
            ],
            'CZ' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'postalCode',
                ],
                'postal_code_pattern' => '\d{3} ?\d{2}',
            ],
            'DK' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'postalCode',
                ],
                'postal_code_pattern' => '\d{4}',
            ],
            'DO' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '\d{5}',
            ],
            'EC' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%postalCode\n%locality",
                'uppercase_fields' => [
                    'locality', 'postalCode',
                ],
                'postal_code_pattern' => '[A-Z]\d{4}[A-Z]|(?:[A-Z]{2})?\d{6}',
            ],
            'EG' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality\n%administrativeArea\n%postalCode",
                'postal_code_pattern' => '\d{5}',
            ],
            'SV' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%postalCode-%locality\n%administrativeArea",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'administrativeArea',
                ],
                'uppercase_fields' => [
                    'locality', 'administrativeArea', 'postalCode',
                ],
                'postal_code_pattern' => 'CP [1-3][1-7][0-2]\d',
            ],
            'EE' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality %administrativeArea",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'postalCode',
                ],
                'administrative_area_type' => 'county',
                'postal_code_pattern' => '\d{5}',
            ],
            'ET' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '\d{4}',
            ],
            'FK' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality\n%postalCode",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'postalCode',
                ],
                'uppercase_fields' => [
                    'locality', 'postalCode',
                ],
                'postal_code_pattern' => 'FIQQ 1ZZ',
            ],
            'FO' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '\d{3}',
                'postal_code_prefix' => 'FO',
            ],
            'FI' => [
                'format' => "%organization\n%recipient\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'postalCode',
                ],
                'postal_code_pattern' => '\d{5}',
                'postal_code_prefix' => 'FI-',
            ],
            'FR' => [
                'format' => "%organization\n%recipient\n%addressLine1\n%addressLine2\n%postalCode %locality %sortingCode",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'postalCode',
                ],
                'uppercase_fields' => [
                    'locality', 'sortingCode',
                ],
                'postal_code_pattern' => '\d{2} ?\d{3}',
            ],
            'GF' => [
                'format' => "%organization\n%recipient\n%addressLine1\n%addressLine2\n%postalCode %locality %sortingCode",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'postalCode',
                ],
                'uppercase_fields' => [
                    'addressLine1', 'addressLine2', 'locality', 'sortingCode',
                ],
                'postal_code_pattern' => '9[78]3\d{2}',
            ],
            'PF' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality %administrativeArea",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'administrativeArea', 'postalCode',
                ],
                'uppercase_fields' => [
                    'locality', 'administrativeArea',
                ],
                'administrative_area_type' => 'island',
                'postal_code_pattern' => '987\d{2}',
            ],
            'GE' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '\d{4}',
            ],
            'DE' => [
                'format' => "%organization\n%recipient\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'postalCode',
                ],
                'postal_code_pattern' => '\d{5}',
            ],
            'GI' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\nGIBRALTAR\n%postalCode",
                'required_fields' => [
                    'recipient', 'addressLine1',
                ],
                'postal_code_pattern' => 'GX11 1AA',
            ],
            'GR' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'postalCode',
                ],
                'postal_code_pattern' => '\d{3} ?\d{2}',
            ],
            'GL' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'postalCode',
                ],
                'postal_code_pattern' => '39\d{2}',
            ],
            'GP' => [
                'format' => "%organization\n%recipient\n%addressLine1\n%addressLine2\n%postalCode %locality %sortingCode",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'postalCode',
                ],
                'uppercase_fields' => [
                    'addressLine1', 'addressLine2', 'locality', 'sortingCode',
                ],
                'postal_code_pattern' => '9[78][01]\d{2}',
            ],
            'GU' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality %administrativeArea %postalCode",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'administrativeArea', 'postalCode',
                ],
                'uppercase_fields' => [
                    'addressLine1', 'addressLine2', 'locality', 'recipient', 'organization', 'administrativeArea',
                ],
                'administrative_area_type' => 'state',
                'postal_code_type' => 'zip',
                'postal_code_pattern' => '(969(?:[12]\d|3[12]))(?:[ \-](\d{4}))?',
            ],
            'GT' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%postalCode- %locality",
                'postal_code_pattern' => '\d{5}',
            ],
            'GG' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality\nGUERNSEY\n%postalCode",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'postalCode',
                ],
                'uppercase_fields' => [
                    'locality', 'postalCode',
                ],
                'postal_code_pattern' => 'GY\d[\dA-Z]? ?\d[ABD-HJLN-UW-Z]{2}',
            ],
            'GN' => [
                'format' => "%recipient\n%organization\n%postalCode %addressLine1\n%addressLine2 %locality",
                'postal_code_pattern' => '\d{3}',
            ],
            'GW' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '\d{4}',
            ],
            'HT' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '\d{4}',
                'postal_code_prefix' => 'HT',
            ],
            'HN' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality, %administrativeArea\n%postalCode",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'administrativeArea',
                ],
                'postal_code_pattern' => '\d{5}',
            ],
            'HK' => [
                'locale' => 'zh-Hant',
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality\n%administrativeArea",
                'local_format' => "%administrativeArea\n%locality\n%addressLine2\n%addressLine1\n%organization\n%recipient",
                'required_fields' => [
                    'recipient', 'addressLine1', 'administrativeArea',
                ],
                'uppercase_fields' => [
                    'administrativeArea',
                ],
                'administrative_area_type' => 'area',
                'locality_type' => 'district',
            ],
            'HU' => [
                'format' => "%recipient\n%organization\n%locality\n%addressLine1\n%addressLine2\n%postalCode",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'postalCode',
                ],
                'uppercase_fields' => [
                    'addressLine1', 'addressLine2', 'locality', 'recipient', 'organization',
                ],
                'postal_code_pattern' => '\d{4}',
            ],
            'IS' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '\d{3}',
            ],
            'IN' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality %postalCode\n%administrativeArea",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'administrativeArea', 'postalCode',
                ],
                'administrative_area_type' => 'state',
                'postal_code_type' => 'pin',
                'postal_code_pattern' => '\d{6}',
            ],
            'ID' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality\n%administrativeArea %postalCode",
                'required_fields' => [
                    'recipient', 'addressLine1', 'administrativeArea',
                ],
                'postal_code_pattern' => '\d{5}',
            ],
            'IR' => [
                'format' => "%organization\n%recipient\n%administrativeArea\n%locality, %dependentLocality\n%addressLine1\n%addressLine2\n%postalCode",
                'dependent_locality_type' => 'neighborhood',
                'postal_code_pattern' => '\d{5}-?\d{5}',
            ],
            'IQ' => [
                'format' => "%organization\n%recipient\n%addressLine1\n%addressLine2\n%locality, %administrativeArea\n%postalCode",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'administrativeArea',
                ],
                'uppercase_fields' => [
                    'locality', 'administrativeArea',
                ],
                'postal_code_pattern' => '\d{5}',
            ],
            'IE' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%dependentLocality\n%locality\n%administrativeArea %postalCode",
                'administrative_area_type' => 'county',
                'dependent_locality_type' => 'townland',
                'postal_code_pattern' => '[\dA-Z]{3} ?[\dA-Z]{4}',
            ],
            'IM' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality\n%postalCode",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'postalCode',
                ],
                'uppercase_fields' => [
                    'locality', 'postalCode',
                ],
                'postal_code_pattern' => 'IM\d[\dA-Z]? ?\d[ABD-HJLN-UW-Z]{2}',
            ],
            'IL' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality %postalCode",
                'postal_code_pattern' => '\d{5}(?:\d{2})?',
            ],
            'IT' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality %administrativeArea",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'administrativeArea', 'postalCode',
                ],
                'uppercase_fields' => [
                    'locality', 'administrativeArea',
                ],
                'postal_code_pattern' => '\d{5}',
            ],
            'JM' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality\n%administrativeArea %sortingCode",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'administrativeArea',
                ],
                'administrative_area_type' => 'parish',
            ],
            'JP' => [
                'locale' => 'ja',
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality, %administrativeArea\n%postalCode",
                'local_format' => "〒%postalCode\n%administrativeArea%locality\n%addressLine2\n%addressLine1\n%organization\n%recipient",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'administrativeArea', 'postalCode',
                ],
                'uppercase_fields' => [
                    'administrativeArea',
                ],
                'administrative_area_type' => 'prefecture',
                'postal_code_pattern' => '\d{3}-?\d{4}',
            ],
            'JE' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality\nJERSEY\n%postalCode",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'postalCode',
                ],
                'uppercase_fields' => [
                    'locality', 'postalCode',
                ],
                'postal_code_pattern' => 'JE\d[\dA-Z]? ?\d[ABD-HJLN-UW-Z]{2}',
            ],
            'JO' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality %postalCode",
                'postal_code_pattern' => '\d{5}',
            ],
            'KZ' => [
                'format' => "%postalCode\n%administrativeArea\n%locality\n%addressLine2\n%addressLine1\n%organization\n%recipient",
                'postal_code_pattern' => '\d{6}',
            ],
            'KE' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality\n%postalCode",
                'postal_code_pattern' => '\d{5}',
            ],
            'KI' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%administrativeArea\n%locality",
                'uppercase_fields' => [
                    'addressLine1', 'addressLine2', 'locality', 'recipient', 'organization', 'administrativeArea',
                ],
                'administrative_area_type' => 'island',
            ],
            'XK' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '[1-7]\d{4}',
            ],
            'KW' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '\d{5}',
            ],
            'KG' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '\d{6}',
            ],
            'LA' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '\d{5}',
            ],
            'LV' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality, %postalCode",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'postalCode',
                ],
                'postal_code_pattern' => 'LV-\d{4}',
            ],
            'LB' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality %postalCode",
                'postal_code_pattern' => '(?:\d{4})(?: ?(?:\d{4}))?',
            ],
            'LS' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality %postalCode",
                'postal_code_pattern' => '\d{3}',
            ],
            'LR' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '\d{4}',
            ],
            'LI' => [
                'format' => "%organization\n%recipient\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'postalCode',
                ],
                'postal_code_pattern' => '948[5-9]|949[0-7]',
                'postal_code_prefix' => 'FL-',
            ],
            'LT' => [
                'format' => "%organization\n%recipient\n%addressLine1\n%addressLine2\n%postalCode %locality %administrativeArea",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'postalCode',
                ],
                'administrative_area_type' => 'county',
                'postal_code_pattern' => '\d{5}',
                'postal_code_prefix' => 'LT-',
            ],
            'LU' => [
                'format' => "%organization\n%recipient\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'postalCode',
                ],
                'postal_code_pattern' => '\d{4}',
                'postal_code_prefix' => 'L-',
            ],
            'MO' => [
                'locale' => 'zh-Hans',
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2",
                'local_format' => "%addressLine2\n%addressLine1\n%organization\n%recipient",
                'required_fields' => [
                    'recipient', 'addressLine1',
                ],
            ],
            'MK' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '\d{4}',
            ],
            'MG' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '\d{3}',
            ],
            'MW' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality %sortingCode",
            ],
            'MY' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%dependentLocality\n%postalCode %locality\n%administrativeArea",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'postalCode',
                ],
                'uppercase_fields' => [
                    'locality', 'administrativeArea',
                ],
                'administrative_area_type' => 'state',
                'dependent_locality_type' => 'village_township',
                'postal_code_pattern' => '\d{5}',
            ],
            'MV' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality %postalCode",
                'postal_code_pattern' => '\d{5}',
            ],
            'MT' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality %postalCode",
                'uppercase_fields' => [
                    'locality', 'postalCode',
                ],
                'postal_code_pattern' => '[A-Z]{3} ?\d{2,4}',
            ],
            'MH' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality %administrativeArea %postalCode",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'administrativeArea', 'postalCode',
                ],
                'uppercase_fields' => [
                    'addressLine1', 'addressLine2', 'locality', 'recipient', 'organization', 'administrativeArea',
                ],
                'administrative_area_type' => 'state',
                'postal_code_type' => 'zip',
                'postal_code_pattern' => '(969[67]\d)(?:[ \-](\d{4}))?',
            ],
            'MQ' => [
                'format' => "%organization\n%recipient\n%addressLine1\n%addressLine2\n%postalCode %locality %sortingCode",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'postalCode',
                ],
                'uppercase_fields' => [
                    'addressLine1', 'addressLine2', 'locality', 'sortingCode',
                ],
                'postal_code_pattern' => '9[78]2\d{2}',
            ],
            'MR' => [
                'uppercase_fields' => [
                    'addressLine1', 'addressLine2', 'locality',
                ],
            ],
            'MU' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%postalCode\n%locality",
                'uppercase_fields' => [
                    'locality', 'postalCode',
                ],
                'postal_code_pattern' => '\d{3}(?:\d{2}|[A-Z]{2}\d{3})',
            ],
            'YT' => [
                'format' => "%organization\n%recipient\n%addressLine1\n%addressLine2\n%postalCode %locality %sortingCode",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'postalCode',
                ],
                'uppercase_fields' => [
                    'addressLine1', 'addressLine2', 'locality', 'sortingCode',
                ],
                'postal_code_pattern' => '976\d{2}',
            ],
            'MX' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%dependentLocality\n%postalCode %locality, %administrativeArea",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'postalCode',
                ],
                'uppercase_fields' => [
                    'locality', 'administrativeArea', 'postalCode',
                ],
                'administrative_area_type' => 'state',
                'dependent_locality_type' => 'neighborhood',
                'postal_code_pattern' => '\d{5}',
            ],
            'FM' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality %administrativeArea %postalCode",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'administrativeArea', 'postalCode',
                ],
                'uppercase_fields' => [
                    'addressLine1', 'addressLine2', 'locality', 'recipient', 'organization', 'administrativeArea',
                ],
                'administrative_area_type' => 'state',
                'postal_code_type' => 'zip',
                'postal_code_pattern' => '(9694[1-4])(?:[ \-](\d{4}))?',
            ],
            'MD' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '\d{4}',
                'postal_code_prefix' => 'MD-',
            ],
            'MC' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality %sortingCode",
                'postal_code_pattern' => '980\d{2}',
                'postal_code_prefix' => 'MC-',
            ],
            'MN' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality\n%administrativeArea %postalCode",
                'postal_code_pattern' => '\d{5}',
            ],
            'ME' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '8\d{4}',
            ],
            'MA' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '\d{5}',
            ],
            'MZ' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '\d{4}',
            ],
            'MM' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality, %postalCode",
                'postal_code_pattern' => '\d{5}',
            ],
            'NR' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%administrativeArea",
                'required_fields' => [
                    'recipient', 'addressLine1', 'administrativeArea',
                ],
                'administrative_area_type' => 'district',
            ],
            'NP' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality %postalCode",
                'postal_code_pattern' => '\d{5}',
            ],
            'NL' => [
                'format' => "%organization\n%recipient\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'postalCode',
                ],
                'postal_code_pattern' => '\d{4} ?[A-Z]{2}',
            ],
            'NC' => [
                'format' => "%organization\n%recipient\n%addressLine1\n%addressLine2\n%postalCode %locality %sortingCode",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'postalCode',
                ],
                'uppercase_fields' => [
                    'addressLine1', 'addressLine2', 'locality', 'sortingCode',
                ],
                'postal_code_pattern' => '988\d{2}',
            ],
            'NZ' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%dependentLocality\n%locality %postalCode",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'postalCode',
                ],
                'postal_code_pattern' => '\d{4}',
            ],
            'NI' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%postalCode\n%locality, %administrativeArea",
                'uppercase_fields' => [
                    'locality', 'administrativeArea',
                ],
                'administrative_area_type' => 'department',
                'postal_code_pattern' => '\d{5}',
            ],
            'NE' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '\d{4}',
            ],
            'NG' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality %postalCode\n%administrativeArea",
                'uppercase_fields' => [
                    'locality', 'administrativeArea',
                ],
                'administrative_area_type' => 'state',
                'postal_code_pattern' => '\d{6}',
            ],
            'NF' => [
                'format' => "%organization\n%recipient\n%addressLine1\n%addressLine2\n%locality %administrativeArea %postalCode",
                'uppercase_fields' => [
                    'locality', 'administrativeArea',
                ],
                'postal_code_pattern' => '2899',
            ],
            'MP' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality %administrativeArea %postalCode",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'administrativeArea', 'postalCode',
                ],
                'uppercase_fields' => [
                    'addressLine1', 'addressLine2', 'locality', 'recipient', 'organization', 'administrativeArea',
                ],
                'administrative_area_type' => 'state',
                'postal_code_type' => 'zip',
                'postal_code_pattern' => '(9695[012])(?:[ \-](\d{4}))?',
            ],
            'NO' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'postalCode',
                ],
                'locality_type' => 'post_town',
                'postal_code_pattern' => '\d{4}',
            ],
            'OM' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%postalCode\n%locality",
                'postal_code_pattern' => '(?:PC )?\d{3}',
            ],
            'PK' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality-%postalCode",
                'postal_code_pattern' => '\d{5}',
            ],
            'PW' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality %administrativeArea %postalCode",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'administrativeArea', 'postalCode',
                ],
                'uppercase_fields' => [
                    'addressLine1', 'addressLine2', 'locality', 'recipient', 'organization', 'administrativeArea',
                ],
                'administrative_area_type' => 'state',
                'postal_code_type' => 'zip',
                'postal_code_pattern' => '(969(?:39|40))(?:[ \-](\d{4}))?',
            ],
            'PA' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality\n%administrativeArea",
                'uppercase_fields' => [
                    'locality', 'administrativeArea',
                ],
            ],
            'PG' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality %postalCode %administrativeArea",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'administrativeArea',
                ],
                'postal_code_pattern' => '\d{3}',
            ],
            'PY' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '\d{4}',
            ],
            'PE' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality %postalCode\n%administrativeArea",
                'postal_code_pattern' => '(?:LIMA \d{1,2}|CALLAO 0?\d)|[0-2]\d{4}',
            ],
            'PH' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%dependentLocality, %locality\n%postalCode %administrativeArea",
                'postal_code_pattern' => '\d{4}',
            ],
            'PN' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality\n%postalCode",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'postalCode',
                ],
                'uppercase_fields' => [
                    'locality', 'postalCode',
                ],
                'postal_code_pattern' => 'PCRN 1ZZ',
            ],
            'PL' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'postalCode',
                ],
                'postal_code_pattern' => '\d{2}-\d{3}',
            ],
            'PT' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'postalCode',
                ],
                'postal_code_pattern' => '\d{4}-\d{3}',
            ],
            'PR' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality %postalCode",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'postalCode',
                ],
                'uppercase_fields' => [
                    'addressLine1', 'addressLine2', 'locality', 'recipient', 'organization',
                ],
                'postal_code_type' => 'zip',
                'postal_code_pattern' => '(00[679]\d{2})(?:[ \-](\d{4}))?',
                'postal_code_prefix' => 'PR ',
            ],
            'QA' => [
                'uppercase_fields' => [
                    'addressLine1', 'addressLine2', 'locality',
                ],
            ],
            'RE' => [
                'format' => "%organization\n%recipient\n%addressLine1\n%addressLine2\n%postalCode %locality %sortingCode",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'postalCode',
                ],
                'uppercase_fields' => [
                    'addressLine1', 'addressLine2', 'locality', 'sortingCode',
                ],
                'postal_code_pattern' => '9[78]4\d{2}',
            ],
            'RO' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'uppercase_fields' => [
                    'addressLine1', 'addressLine2', 'locality',
                ],
                'postal_code_pattern' => '\d{6}',
            ],
            'RU' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality\n%administrativeArea\n%postalCode",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'administrativeArea', 'postalCode',
                ],
                'uppercase_fields' => [
                    'addressLine1', 'addressLine2', 'locality',
                ],
                'administrative_area_type' => 'oblast',
                'postal_code_pattern' => '\d{6}',
            ],
            'RW' => [
                'uppercase_fields' => [
                    'addressLine1', 'addressLine2', 'locality',
                ],
            ],
            'SM' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'required_fields' => [
                    'recipient', 'addressLine1', 'postalCode',
                ],
                'postal_code_pattern' => '4789\d',
            ],
            'SA' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality %postalCode",
                'postal_code_pattern' => '\d{5}',
            ],
            'SN' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '\d{5}',
            ],
            'RS' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '\d{5,6}',
            ],
            'SC' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality\n%administrativeArea",
                'uppercase_fields' => [
                    'administrativeArea',
                ],
                'administrative_area_type' => 'island',
            ],
            'SG' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\nSINGAPORE %postalCode",
                'required_fields' => [
                    'recipient', 'addressLine1', 'postalCode',
                ],
                'postal_code_pattern' => '\d{6}',
            ],
            'SK' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'postalCode',
                ],
                'postal_code_pattern' => '\d{3} ?\d{2}',
            ],
            'SI' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '\d{4}',
                'postal_code_prefix' => 'SI- ',
            ],
            'SO' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality, %administrativeArea %postalCode",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'administrativeArea',
                ],
                'uppercase_fields' => [
                    'addressLine1', 'addressLine2', 'locality', 'administrativeArea',
                ],
                'postal_code_pattern' => '[A-Z]{2} ?\d{5}',
            ],
            'ZA' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%dependentLocality\n%locality\n%postalCode",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'postalCode',
                ],
                'postal_code_pattern' => '\d{4}',
            ],
            'GS' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n\n%locality\n%postalCode",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'postalCode',
                ],
                'uppercase_fields' => [
                    'locality', 'postalCode',
                ],
                'postal_code_pattern' => 'SIQQ 1ZZ',
            ],
            'KR' => [
                'locale' => 'ko',
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%dependentLocality\n%locality\n%administrativeArea\n%postalCode",
                'local_format' => "%administrativeArea %locality%dependentLocality\n%addressLine2\n%addressLine1\n%organization\n%recipient\n%postalCode",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'administrativeArea', 'postalCode',
                ],
                'uppercase_fields' => [
                    'postalCode',
                ],
                'administrative_area_type' => 'do_si',
                'dependent_locality_type' => 'district',
                'postal_code_pattern' => '\d{3}(?:\d{2}|-\d{3})',
            ],
            'ES' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality %administrativeArea",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'administrativeArea', 'postalCode',
                ],
                'uppercase_fields' => [
                    'locality', 'administrativeArea',
                ],
                'postal_code_pattern' => '\d{5}',
            ],
            'LK' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality\n%postalCode",
                'postal_code_pattern' => '\d{5}',
            ],
            'BL' => [
                'format' => "%organization\n%recipient\n%addressLine1\n%addressLine2\n%postalCode %locality %sortingCode",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'postalCode',
                ],
                'uppercase_fields' => [
                    'addressLine1', 'addressLine2', 'locality', 'sortingCode',
                ],
                'postal_code_pattern' => '9[78][01]\d{2}',
            ],
            'SH' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality\n%postalCode",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'postalCode',
                ],
                'uppercase_fields' => [
                    'locality', 'postalCode',
                ],
                'postal_code_pattern' => '(?:ASCN|STHL) 1ZZ',
            ],
            'KN' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality, %administrativeArea",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'administrativeArea',
                ],
                'administrative_area_type' => 'island',
            ],
            'MF' => [
                'format' => "%organization\n%recipient\n%addressLine1\n%addressLine2\n%postalCode %locality %sortingCode",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'postalCode',
                ],
                'uppercase_fields' => [
                    'addressLine1', 'addressLine2', 'locality', 'sortingCode',
                ],
                'postal_code_pattern' => '9[78][01]\d{2}',
            ],
            'PM' => [
                'format' => "%organization\n%recipient\n%addressLine1\n%addressLine2\n%postalCode %locality %sortingCode",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'postalCode',
                ],
                'uppercase_fields' => [
                    'addressLine1', 'addressLine2', 'locality', 'sortingCode',
                ],
                'postal_code_pattern' => '9[78]5\d{2}',
            ],
            'VC' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality %postalCode",
                'postal_code_pattern' => 'VC\d{4}',
            ],
            'SR' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality\n%administrativeArea",
                'uppercase_fields' => [
                    'addressLine1', 'addressLine2', 'administrativeArea',
                ],
            ],
            'SJ' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'postalCode',
                ],
                'locality_type' => 'post_town',
                'postal_code_pattern' => '\d{4}',
            ],
            'SZ' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality\n%postalCode",
                'uppercase_fields' => [
                    'addressLine1', 'addressLine2', 'locality', 'postalCode',
                ],
                'postal_code_pattern' => '[HLMS]\d{3}',
            ],
            'SE' => [
                'format' => "%organization\n%recipient\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'postalCode',
                ],
                'locality_type' => 'post_town',
                'postal_code_pattern' => '\d{3} ?\d{2}',
                'postal_code_prefix' => 'SE-',
            ],
            'CH' => [
                'format' => "%organization\n%recipient\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'postalCode',
                ],
                'uppercase_fields' => [
                ],
                'postal_code_pattern' => '\d{4}',
                'postal_code_prefix' => 'CH-',
            ],
            'TW' => [
                'locale' => 'zh-Hant',
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality, %administrativeArea %postalCode",
                'local_format' => "%postalCode\n%administrativeArea%locality\n%addressLine2\n%addressLine1\n%organization\n%recipient",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'administrativeArea', 'postalCode',
                ],
                'administrative_area_type' => 'county',
                'postal_code_pattern' => '\d{3}(?:\d{2})?',
            ],
            'TJ' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '\d{6}',
            ],
            'TZ' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '\d{4,5}',
            ],
            'TH' => [
                'locale' => 'th',
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%dependentLocality, %locality\n%administrativeArea %postalCode",
                'local_format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%dependentLocality %locality\n%administrativeArea %postalCode",
                'uppercase_fields' => [
                    'administrativeArea',
                ],
                'postal_code_pattern' => '\d{5}',
            ],
            'TA' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality\n%postalCode",
                'postal_code_pattern' => 'TDCU 1ZZ',
            ],
            'TN' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '\d{4}',
            ],
            'TR' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality/%administrativeArea",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'postalCode',
                ],
                'locality_type' => 'district',
                'postal_code_pattern' => '\d{5}',
            ],
            'TM' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '\d{6}',
            ],
            'TC' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality\n%postalCode",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'postalCode',
                ],
                'uppercase_fields' => [
                    'locality', 'postalCode',
                ],
                'postal_code_pattern' => 'TKCA 1ZZ',
            ],
            'TV' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality\n%administrativeArea",
                'uppercase_fields' => [
                    'addressLine1', 'addressLine2', 'locality', 'administrativeArea',
                ],
                'administrative_area_type' => 'island',
            ],
            'UM' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality %administrativeArea %postalCode",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'administrativeArea',
                ],
                'uppercase_fields' => [
                    'addressLine1', 'addressLine2', 'locality', 'recipient', 'organization', 'administrativeArea',
                ],
                'administrative_area_type' => 'state',
                'postal_code_type' => 'zip',
                'postal_code_pattern' => '96898',
            ],
            'VI' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality %administrativeArea %postalCode",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'administrativeArea', 'postalCode',
                ],
                'uppercase_fields' => [
                    'addressLine1', 'addressLine2', 'locality', 'recipient', 'organization', 'administrativeArea',
                ],
                'administrative_area_type' => 'state',
                'postal_code_type' => 'zip',
                'postal_code_pattern' => '(008(?:(?:[0-4]\d)|(?:5[01])))(?:[ \-](\d{4}))?',
            ],
            'UA' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality\n%administrativeArea\n%postalCode",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'administrativeArea', 'postalCode',
                ],
                'administrative_area_type' => 'oblast',
                'postal_code_pattern' => '\d{5}',
            ],
            'AE' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%administrativeArea",
                'required_fields' => [
                    'recipient', 'addressLine1', 'administrativeArea',
                ],
                'administrative_area_type' => 'emirate',
            ],
            'GB' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality\n%postalCode",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'postalCode',
                ],
                'uppercase_fields' => [
                    'locality', 'postalCode',
                ],
                'locality_type' => 'post_town',
                'postal_code_pattern' => 'GIR ?0AA|(?:(?:AB|AL|B|BA|BB|BD|BH|BL|BN|BR|BS|BT|BX|CA|CB|CF|CH|CM|CO|CR|CT|CV|CW|DA|DD|DE|DG|DH|DL|DN|DT|DY|E|EC|EH|EN|EX|FK|FY|G|GL|GY|GU|HA|HD|HG|HP|HR|HS|HU|HX|IG|IM|IP|IV|JE|KA|KT|KW|KY|L|LA|LD|LE|LL|LN|LS|LU|M|ME|MK|ML|N|NE|NG|NN|NP|NR|NW|OL|OX|PA|PE|PH|PL|PO|PR|RG|RH|RM|S|SA|SE|SG|SK|SL|SM|SN|SO|SP|SR|SS|ST|SW|SY|TA|TD|TF|TN|TQ|TR|TS|TW|UB|W|WA|WC|WD|WF|WN|WR|WS|WV|YO|ZE)(?:\d[\dA-Z]? ?\d[ABD-HJLN-UW-Z]{2}))|BFPO ?\d{1,4}',
            ],
            'US' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality, %administrativeArea %postalCode",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'administrativeArea', 'postalCode',
                ],
                'uppercase_fields' => [
                    'locality', 'administrativeArea',
                ],
                'administrative_area_type' => 'state',
                'postal_code_type' => 'zip',
                'postal_code_pattern' => '(\d{5})(?:[ \-](\d{4}))?',
            ],
            'UY' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality %administrativeArea",
                'uppercase_fields' => [
                    'locality', 'administrativeArea',
                ],
                'postal_code_pattern' => '\d{5}',
            ],
            'UZ' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality\n%administrativeArea",
                'uppercase_fields' => [
                    'locality', 'administrativeArea',
                ],
                'postal_code_pattern' => '\d{6}',
            ],
            'VA' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '00120',
            ],
            'VE' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality %postalCode, %administrativeArea",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'administrativeArea',
                ],
                'uppercase_fields' => [
                    'locality', 'administrativeArea',
                ],
                'administrative_area_type' => 'state',
                'postal_code_pattern' => '\d{4}',
            ],
            'VN' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality\n%administrativeArea %postalCode",
                'postal_code_pattern' => '\d{6}',
            ],
            'WF' => [
                'format' => "%organization\n%recipient\n%addressLine1\n%addressLine2\n%postalCode %locality %sortingCode",
                'required_fields' => [
                    'recipient', 'addressLine1', 'locality', 'postalCode',
                ],
                'uppercase_fields' => [
                    'addressLine1', 'addressLine2', 'locality', 'sortingCode',
                ],
                'postal_code_pattern' => '986\d{2}',
            ],
            'EH' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '\d{5}',
            ],
            'ZM' => [
                'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '\d{5}',
            ],
        ];
        // @codingStandardsIgnoreEnd

        return $definitions;
    }
}
