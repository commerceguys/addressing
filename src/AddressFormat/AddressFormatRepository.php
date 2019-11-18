<?php

namespace CommerceGuys\Addressing\AddressFormat;

class AddressFormatRepository implements AddressFormatRepositoryInterface
{
    /**
     * The instantiated address formats, keyed by country code.
     *
     * @var array
     */
    protected $addressFormats = [];

    /**
     * {@inheritdoc}
     */
    public function get($countryCode)
    {
        $countryCode = strtoupper($countryCode);
        if (!isset($this->addressFormats[$countryCode])) {
            $definitions = $this->getDefinitions();
            $definition = isset($definitions[$countryCode]) ? $definitions[$countryCode] : [];
            $definition = $this->processDefinition($countryCode, $definition);
            $this->addressFormats[$countryCode] = new AddressFormat($definition);
        }

        return $this->addressFormats[$countryCode];
    }

    /**
     * {@inheritdoc}
     */
    public function getAll()
    {
        $definitions = $this->getDefinitions();
        $addressFormats = [];
        foreach ($definitions as $countryCode => $definition) {
            $definition = $this->processDefinition($countryCode, $definition);
            $addressFormats[$countryCode] = new AddressFormat($definition);
        }

        return $addressFormats;
    }

    /**
     * Processes the country's address format definition.
     *
     * @param string $countryCode The country code.
     * @param array $definition   The definition.
     *
     * @return array The processed definition.
     */
    protected function processDefinition($countryCode, array $definition)
    {
        $definition['country_code'] = $countryCode;
        // Merge-in defaults.
        $definition += $this->getGenericDefinition();
        // Always require the given name and family name.
        $definition['required_fields'][] = AddressField::GIVEN_NAME;
        $definition['required_fields'][] = AddressField::FAMILY_NAME;

        return $definition;
    }

    /**
     * Gets the generic address format definition.
     *
     * @return array The generic address format definition.
     */
    protected function getGenericDefinition()
    {
        return [
            'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality",
            'required_fields' => [
                'addressLine1', 'locality',
            ],
            'uppercase_fields' => [
                'locality',
            ],
            'administrative_area_type' => 'province',
            'locality_type' => 'city',
            'dependent_locality_type' => 'suburb',
            'postal_code_type' => 'postal',
            'subdivision_depth' => 0,
        ];
    }

    /**
     * Gets the address format definitions.
     *
     * @return array The address format definitions.
     */
    protected function getDefinitions()
    {
        // @codingStandardsIgnoreStart
        $definitions = [
            'AC' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality\n%postalCode",
                'postal_code_pattern' => 'ASCN 1ZZ',
                'postal_code_example' => 'ASCN 1ZZ',
            ],
            'AD' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => 'AD[1-7]0\d',
                'postal_code_example' => 'AD100',
                'subdivision_depth' => 1,
            ],
            'AE' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%administrativeArea",
                'required_fields' => [
                    'addressLine1', 'administrativeArea',
                ],
                'administrative_area_type' => 'emirate',
                'subdivision_depth' => 1,
            ],
            'AF' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality\n%postalCode",
                'postal_code_pattern' => '\d{4}',
                'postal_code_example' => '1001',
            ],
            'AG' => [
                'required_fields' => [
                    'addressLine1',
                ],
            ],
            'AI' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality\n%postalCode",
                'postal_code_pattern' => '(?:AI-)?2640',
                'postal_code_example' => '2640',
            ],
            'AL' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%postalCode\n%locality",
                'postal_code_pattern' => '\d{4}',
                'postal_code_example' => '1001',
            ],
            'AM' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%postalCode\n%locality\n%administrativeArea",
                'postal_code_pattern' => '(?:37)?\d{4}',
                'postal_code_example' => '375010',
                'subdivision_depth' => 1,
            ],
            'AR' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality\n%administrativeArea",
                'uppercase_fields' => [
                    'addressLine1', 'addressLine2', 'locality', 'postalCode',
                ],
                'postal_code_pattern' => '((?:[A-HJ-NP-Z])?\d{4})([A-Z]{3})?',
                'postal_code_example' => 'C1070AAM',
                'subdivision_depth' => 1,
            ],
            'AS' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality %administrativeArea %postalCode",
                'required_fields' => [
                    'addressLine1', 'locality', 'administrativeArea', 'postalCode',
                ],
                'uppercase_fields' => [
                    'addressLine1', 'addressLine2', 'locality', 'familyName', 'additionalName', 'givenName', 'organization', 'administrativeArea',
                ],
                'administrative_area_type' => 'state',
                'postal_code_type' => 'zip',
                'postal_code_pattern' => '(96799)(?:[ \-](\d{4}))?',
                'postal_code_example' => '96799',
            ],
            'AT' => [
                'format' => "%organization\n%givenName %familyName\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'required_fields' => [
                    'addressLine1', 'locality', 'postalCode',
                ],
                'postal_code_pattern' => '\d{4}',
                'postal_code_example' => '1010',
            ],
            'AU' => [
                'format' => "%organization\n%givenName %familyName\n%addressLine1\n%addressLine2\n%locality %administrativeArea %postalCode",
                'required_fields' => [
                    'addressLine1', 'locality', 'administrativeArea', 'postalCode',
                ],
                'uppercase_fields' => [
                    'locality', 'administrativeArea',
                ],
                'administrative_area_type' => 'state',
                'locality_type' => 'suburb',
                'postal_code_pattern' => '\d{4}',
                'postal_code_example' => '2060',
                'subdivision_depth' => 1,
            ],
            'AX' => [
                'format' => "%organization\n%givenName %familyName\n%addressLine1\n%addressLine2\n%postalCode %locality\nÅLAND",
                'required_fields' => [
                    'addressLine1', 'locality', 'postalCode',
                ],
                'postal_code_pattern' => '22\d{3}',
                'postal_code_example' => '22150',
                'postal_code_prefix' => 'AX-',
            ],
            'AZ' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '\d{4}',
                'postal_code_example' => '1000',
                'postal_code_prefix' => 'AZ ',
            ],
            'BA' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '\d{5}',
                'postal_code_example' => '71000',
            ],
            'BB' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality, %administrativeArea %postalCode",
                'administrative_area_type' => 'parish',
                'postal_code_pattern' => 'BB\d{5}',
                'postal_code_example' => 'BB23026',
            ],
            'BD' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality - %postalCode",
                'postal_code_pattern' => '\d{4}',
                'postal_code_example' => '1340',
            ],
            'BE' => [
                'format' => "%organization\n%givenName %familyName\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'required_fields' => [
                    'addressLine1', 'locality', 'postalCode',
                ],
                'postal_code_pattern' => '\d{4}',
                'postal_code_example' => '4000',
            ],
            'BF' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality %sortingCode",
            ],
            'BG' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '\d{4}',
                'postal_code_example' => '1000',
            ],
            'BH' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality %postalCode",
                'postal_code_pattern' => '(?:\d|1[0-2])\d{2}',
                'postal_code_example' => '317',
            ],
            'BJ' => [
                'uppercase_fields' => [
                    'addressLine1', 'addressLine2', 'locality',
                ],
            ],
            'BL' => [
                'format' => "%organization\n%givenName %familyName\n%addressLine1\n%addressLine2\n%postalCode %locality %sortingCode",
                'required_fields' => [
                    'addressLine1', 'locality', 'postalCode',
                ],
                'uppercase_fields' => [
                    'addressLine1', 'addressLine2', 'locality', 'sortingCode',
                ],
                'postal_code_pattern' => '9[78][01]\d{2}',
                'postal_code_example' => '97100',
            ],
            'BM' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality %postalCode",
                'postal_code_pattern' => '[A-Z]{2} ?[A-Z0-9]{2}',
                'postal_code_example' => 'FL 07',
            ],
            'BN' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality %postalCode",
                'postal_code_pattern' => '[A-Z]{2} ?\d{4}',
                'postal_code_example' => 'BT2328',
            ],
            'BO' => [
                'uppercase_fields' => [
                    'addressLine1', 'addressLine2', 'locality',
                ],
            ],
            'BR' => [
                'format' => "%organization\n%givenName %familyName\n%addressLine1\n%addressLine2\n%dependentLocality\n%locality-%administrativeArea\n%postalCode",
                'required_fields' => [
                    'addressLine1', 'administrativeArea', 'locality', 'postalCode',
                ],
                'uppercase_fields' => [
                    'locality', 'administrativeArea',
                ],
                'administrative_area_type' => 'state',
                'dependent_locality_type' => 'neighborhood',
                'postal_code_pattern' => '\d{5}-?\d{3}',
                'postal_code_example' => '40301-110',
                'subdivision_depth' => 2,
            ],
            'BS' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality, %administrativeArea",
                'administrative_area_type' => 'island',
                'subdivision_depth' => 1,
            ],
            'BT' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality %postalCode",
                'postal_code_pattern' => '\d{5}',
                'postal_code_example' => '11001',
            ],
            'BY' => [
                'format' => "%administrativeArea\n%postalCode %locality\n%addressLine1\n%addressLine2\n%organization\n%givenName %familyName",
                'postal_code_pattern' => '\d{6}',
                'postal_code_example' => '223016',
            ],
            'CA' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality %administrativeArea %postalCode",
                'required_fields' => [
                    'addressLine1', 'locality', 'administrativeArea', 'postalCode',
                ],
                'uppercase_fields' => [
                    'addressLine1', 'addressLine2', 'locality', 'familyName', 'additionalName', 'givenName', 'organization', 'administrativeArea', 'postalCode',
                ],
                'postal_code_pattern' => '[ABCEGHJKLMNPRSTVXY]\d[ABCEGHJ-NPRSTV-Z] ?\d[ABCEGHJ-NPRSTV-Z]\d',
                'postal_code_example' => 'H3Z 2Y7',
                'subdivision_depth' => 1,
            ],
            'CC' => [
                'format' => "%organization\n%givenName %familyName\n%addressLine1\n%addressLine2\n%locality %administrativeArea %postalCode",
                'uppercase_fields' => [
                    'locality', 'administrativeArea',
                ],
                'postal_code_pattern' => '6799',
                'postal_code_example' => '6799',
            ],
            'CH' => [
                'format' => "%organization\n%givenName %familyName\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'required_fields' => [
                    'addressLine1', 'locality', 'postalCode',
                ],
                'uppercase_fields' => [],
                'postal_code_pattern' => '\d{4}',
                'postal_code_example' => '2544',
                'postal_code_prefix' => 'CH-',
            ],
            'CI' => [
                'format' => "%givenName %familyName\n%organization\n%sortingCode %addressLine1\n%addressLine2 %locality %sortingCode",
            ],
            'CL' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality\n%administrativeArea",
                'postal_code_pattern' => '\d{7}',
                'postal_code_example' => '8340457',
                'subdivision_depth' => 2,
            ],
            'CN' => [
                'locale' => 'zh-Hans',
                'format' => "%familyName %givenName\n%organization\n%addressLine1\n%addressLine2\n%dependentLocality\n%locality\n%administrativeArea, %postalCode",
                'local_format' => "%postalCode\n%administrativeArea%locality%dependentLocality\n%addressLine1\n%addressLine2\n%organization\n%familyName %givenName",
                'required_fields' => [
                    'addressLine1', 'locality', 'administrativeArea', 'postalCode',
                ],
                'uppercase_fields' => [
                    'administrativeArea',
                ],
                'dependent_locality_type' => 'district',
                'postal_code_pattern' => '\d{6}',
                'postal_code_example' => '266033',
                'subdivision_depth' => 3,
            ],
            'CO' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality, %administrativeArea, %postalCode",
                'required_fields' => [
                    'addressLine1', 'locality', 'administrativeArea',
                ],
                'administrative_area_type' => 'department',
                'postal_code_pattern' => '\d{6}',
                'postal_code_example' => '111221',
                'subdivision_depth' => 1,
            ],
            'CR' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%administrativeArea, %locality\n%postalCode",
                'required_fields' => [
                    'addressLine1', 'locality', 'administrativeArea',
                ],
                'postal_code_pattern' => '\d{4,5}|\d{3}-\d{4}',
                'postal_code_example' => '1000',
            ],
            'CU' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality %administrativeArea\n%postalCode",
                'postal_code_pattern' => '\d{5}',
                'postal_code_example' => '10700',
                'subdivision_depth' => 1,
            ],
            'CV' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality\n%administrativeArea",
                'administrative_area_type' => 'island',
                'postal_code_pattern' => '\d{4}',
                'postal_code_example' => '7600',
                'subdivision_depth' => 1,
            ],
            'CX' => [
                'format' => "%organization\n%givenName %familyName\n%addressLine1\n%addressLine2\n%locality %administrativeArea %postalCode",
                'uppercase_fields' => [
                    'locality', 'administrativeArea',
                ],
                'postal_code_pattern' => '6798',
                'postal_code_example' => '6798',
            ],
            'CY' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '\d{4}',
                'postal_code_example' => '2008',
            ],
            'CZ' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'required_fields' => [
                    'addressLine1', 'locality', 'postalCode',
                ],
                'postal_code_pattern' => '\d{3} ?\d{2}',
                'postal_code_example' => '100 00',
            ],
            'DE' => [
                'format' => "%organization\n%givenName %familyName\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'required_fields' => [
                    'addressLine1', 'locality', 'postalCode',
                ],
                'postal_code_pattern' => '\d{5}',
                'postal_code_example' => '26133',
            ],
            'DK' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'required_fields' => [
                    'addressLine1', 'locality', 'postalCode',
                ],
                'postal_code_pattern' => '\d{4}',
                'postal_code_example' => '8660',
            ],
            'DO' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '\d{5}',
                'postal_code_example' => '11903',
            ],
            'DZ' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '\d{5}',
                'postal_code_example' => '40304',
            ],
            'EC' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%postalCode\n%locality",
                'uppercase_fields' => [
                    'locality', 'postalCode',
                ],
                'postal_code_pattern' => '\d{6}',
                'postal_code_example' => '090105',
            ],
            'EE' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality %administrativeArea",
                'required_fields' => [
                    'addressLine1', 'locality', 'postalCode',
                ],
                'administrative_area_type' => 'county',
                'postal_code_pattern' => '\d{5}',
                'postal_code_example' => '69501',
            ],
            'EG' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality\n%administrativeArea\n%postalCode",
                'postal_code_pattern' => '\d{5}',
                'postal_code_example' => '12411',
                'subdivision_depth' => 1,
            ],
            'EH' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '\d{5}',
                'postal_code_example' => '70000',
            ],
            'ES' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality %administrativeArea",
                'required_fields' => [
                    'addressLine1', 'locality', 'administrativeArea', 'postalCode',
                ],
                'uppercase_fields' => [
                    'locality', 'administrativeArea',
                ],
                'postal_code_pattern' => '\d{5}',
                'postal_code_example' => '28039',
                'subdivision_depth' => 1,
            ],
            'ET' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '\d{4}',
                'postal_code_example' => '1000',
            ],
            'FI' => [
                'format' => "%organization\n%givenName %familyName\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'required_fields' => [
                    'addressLine1', 'locality', 'postalCode',
                ],
                'postal_code_pattern' => '\d{5}',
                'postal_code_example' => '00550',
                'postal_code_prefix' => 'FI-',
            ],
            'FK' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality\n%postalCode",
                'required_fields' => [
                    'addressLine1', 'locality', 'postalCode',
                ],
                'uppercase_fields' => [
                    'locality', 'postalCode',
                ],
                'postal_code_pattern' => 'FIQQ 1ZZ',
                'postal_code_example' => 'FIQQ 1ZZ',
            ],
            'FM' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality %administrativeArea %postalCode",
                'required_fields' => [
                    'addressLine1', 'locality', 'administrativeArea', 'postalCode',
                ],
                'uppercase_fields' => [
                    'addressLine1', 'addressLine2', 'locality', 'familyName', 'additionalName', 'givenName', 'organization', 'administrativeArea',
                ],
                'administrative_area_type' => 'state',
                'postal_code_type' => 'zip',
                'postal_code_pattern' => '(9694[1-4])(?:[ \-](\d{4}))?',
                'postal_code_example' => '96941',
            ],
            'FO' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '\d{3}',
                'postal_code_example' => '100',
                'postal_code_prefix' => 'FO',
            ],
            'FR' => [
                'format' => "%organization\n%givenName %familyName\n%addressLine1\n%addressLine2\n%postalCode %locality %sortingCode",
                'required_fields' => [
                    'addressLine1', 'locality', 'postalCode',
                ],
                'uppercase_fields' => [
                    'locality', 'sortingCode',
                ],
                'postal_code_pattern' => '\d{2} ?\d{3}',
                'postal_code_example' => '33380',
            ],
            'GB' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality\n%postalCode",
                'required_fields' => [
                    'addressLine1', 'locality', 'postalCode',
                ],
                'uppercase_fields' => [
                    'locality', 'postalCode',
                ],
                'locality_type' => 'post_town',
                'postal_code_pattern' => 'GIR ?0AA|(?:(?:AB|AL|B|BA|BB|BD|BF|BH|BL|BN|BR|BS|BT|BX|CA|CB|CF|CH|CM|CO|CR|CT|CV|CW|DA|DD|DE|DG|DH|DL|DN|DT|DY|E|EC|EH|EN|EX|FK|FY|G|GL|GY|GU|HA|HD|HG|HP|HR|HS|HU|HX|IG|IM|IP|IV|JE|KA|KT|KW|KY|L|LA|LD|LE|LL|LN|LS|LU|M|ME|MK|ML|N|NE|NG|NN|NP|NR|NW|OL|OX|PA|PE|PH|PL|PO|PR|RG|RH|RM|S|SA|SE|SG|SK|SL|SM|SN|SO|SP|SR|SS|ST|SW|SY|TA|TD|TF|TN|TQ|TR|TS|TW|UB|W|WA|WC|WD|WF|WN|WR|WS|WV|YO|ZE)(?:\d[\dA-Z]? ?\d[ABD-HJLN-UW-Z]{2}))|BFPO ?\d{1,4}',
                'postal_code_example' => 'EC1Y 8SY',
            ],
            'GE' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '\d{4}',
                'postal_code_example' => '0101',
            ],
            'GF' => [
                'format' => "%organization\n%givenName %familyName\n%addressLine1\n%addressLine2\n%postalCode %locality %sortingCode",
                'required_fields' => [
                    'addressLine1', 'locality', 'postalCode',
                ],
                'uppercase_fields' => [
                    'addressLine1', 'addressLine2', 'locality', 'sortingCode',
                ],
                'postal_code_pattern' => '9[78]3\d{2}',
                'postal_code_example' => '97300',
            ],
            'GG' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality\nGUERNSEY\n%postalCode",
                'required_fields' => [
                    'addressLine1', 'locality', 'postalCode',
                ],
                'uppercase_fields' => [
                    'locality', 'postalCode',
                ],
                'postal_code_pattern' => 'GY\d[\dA-Z]? ?\d[ABD-HJLN-UW-Z]{2}',
                'postal_code_example' => 'GY1 1AA',
            ],
            'GI' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\nGIBRALTAR\n%postalCode",
                'required_fields' => [
                    'addressLine1',
                ],
                'postal_code_pattern' => 'GX11 1AA',
                'postal_code_example' => 'GX11 1AA',
            ],
            'GL' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'required_fields' => [
                    'addressLine1', 'locality', 'postalCode',
                ],
                'postal_code_pattern' => '39\d{2}',
                'postal_code_example' => '3900',
            ],
            'GN' => [
                'format' => "%givenName %familyName\n%organization\n%postalCode %addressLine1\n%addressLine2 %locality",
                'postal_code_pattern' => '\d{3}',
                'postal_code_example' => '001',
            ],
            'GP' => [
                'format' => "%organization\n%givenName %familyName\n%addressLine1\n%addressLine2\n%postalCode %locality %sortingCode",
                'required_fields' => [
                    'addressLine1', 'locality', 'postalCode',
                ],
                'uppercase_fields' => [
                    'addressLine1', 'addressLine2', 'locality', 'sortingCode',
                ],
                'postal_code_pattern' => '9[78][01]\d{2}',
                'postal_code_example' => '97100',
            ],
            'GR' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'required_fields' => [
                    'addressLine1', 'locality', 'postalCode',
                ],
                'postal_code_pattern' => '\d{3} ?\d{2}',
                'postal_code_example' => '151 24',
            ],
            'GS' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n\n%locality\n%postalCode",
                'required_fields' => [
                    'addressLine1', 'locality', 'postalCode',
                ],
                'uppercase_fields' => [
                    'locality', 'postalCode',
                ],
                'postal_code_pattern' => 'SIQQ 1ZZ',
                'postal_code_example' => 'SIQQ 1ZZ',
            ],
            'GT' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%postalCode- %locality",
                'postal_code_pattern' => '\d{5}',
                'postal_code_example' => '09001',
            ],
            'GU' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality %postalCode",
                'required_fields' => [
                    'addressLine1', 'locality', 'postalCode',
                ],
                'uppercase_fields' => [
                    'addressLine1', 'addressLine2', 'locality', 'familyName', 'additionalName', 'givenName', 'organization',
                ],
                'postal_code_type' => 'zip',
                'postal_code_pattern' => '(969(?:[12]\d|3[12]))(?:[ \-](\d{4}))?',
                'postal_code_example' => '96910',
            ],
            'GW' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '\d{4}',
                'postal_code_example' => '1000',
            ],
            'HK' => [
                'locale' => 'zh-Hant',
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality\n%administrativeArea",
                'local_format' => "%administrativeArea\n%locality\n%addressLine1\n%addressLine2\n%organization\n%givenName %familyName",
                'required_fields' => [
                    'addressLine1', 'administrativeArea',
                ],
                'uppercase_fields' => [
                    'administrativeArea',
                ],
                'administrative_area_type' => 'area',
                'locality_type' => 'district',
                'subdivision_depth' => 2,
            ],
            'HM' => [
                'format' => "%organization\n%givenName %familyName\n%addressLine1\n%addressLine2\n%locality %administrativeArea %postalCode",
                'uppercase_fields' => [
                    'locality', 'administrativeArea',
                ],
                'postal_code_pattern' => '\d{4}',
                'postal_code_example' => '7050',
            ],
            'HN' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality, %administrativeArea\n%postalCode",
                'required_fields' => [
                    'addressLine1', 'locality', 'administrativeArea',
                ],
                'postal_code_pattern' => '\d{5}',
                'postal_code_example' => '31301',
            ],
            'HR' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '\d{5}',
                'postal_code_example' => '10000',
                'postal_code_prefix' => 'HR-',
            ],
            'HT' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '\d{4}',
                'postal_code_example' => '6120',
                'postal_code_prefix' => 'HT',
            ],
            'HU' => [
                'format' => "%familyName %givenName\n%organization\n%locality\n%addressLine1\n%addressLine2\n%postalCode",
                'required_fields' => [
                    'addressLine1', 'locality', 'postalCode',
                ],
                'uppercase_fields' => [
                    'addressLine1', 'addressLine2', 'locality', 'familyName', 'additionalName', 'givenName', 'organization',
                ],
                'postal_code_pattern' => '\d{4}',
                'postal_code_example' => '1037',
            ],
            'ID' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality\n%administrativeArea %postalCode",
                'required_fields' => [
                    'addressLine1', 'administrativeArea',
                ],
                'postal_code_pattern' => '\d{5}',
                'postal_code_example' => '40115',
                'subdivision_depth' => 1,
            ],
            'IE' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%dependentLocality\n%locality\n%administrativeArea %postalCode",
                'administrative_area_type' => 'county',
                'dependent_locality_type' => 'townland',
                'postal_code_type' => 'eircode',
                'postal_code_pattern' => '[\dA-Z]{3} ?[\dA-Z]{4}',
                'postal_code_example' => 'A65 F4E2',
                'subdivision_depth' => 1,
            ],
            'IL' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality %postalCode",
                'postal_code_pattern' => '\d{5}(?:\d{2})?',
                'postal_code_example' => '9614303',
            ],
            'IM' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality\n%postalCode",
                'required_fields' => [
                    'addressLine1', 'locality', 'postalCode',
                ],
                'uppercase_fields' => [
                    'locality', 'postalCode',
                ],
                'postal_code_pattern' => 'IM\d[\dA-Z]? ?\d[ABD-HJLN-UW-Z]{2}',
                'postal_code_example' => 'IM2 1AA',
            ],
            'IN' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality %postalCode\n%administrativeArea",
                'required_fields' => [
                    'addressLine1', 'locality', 'administrativeArea', 'postalCode',
                ],
                'administrative_area_type' => 'state',
                'postal_code_type' => 'pin',
                'postal_code_pattern' => '\d{6}',
                'postal_code_example' => '110034',
                'subdivision_depth' => 1,
            ],
            'IO' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality\n%postalCode",
                'required_fields' => [
                    'addressLine1', 'locality', 'postalCode',
                ],
                'uppercase_fields' => [
                    'locality', 'postalCode',
                ],
                'postal_code_pattern' => 'BBND 1ZZ',
                'postal_code_example' => 'BBND 1ZZ',
            ],
            'IQ' => [
                'format' => "%organization\n%givenName %familyName\n%addressLine1\n%addressLine2\n%locality, %administrativeArea\n%postalCode",
                'required_fields' => [
                    'addressLine1', 'locality', 'administrativeArea',
                ],
                'uppercase_fields' => [
                    'locality', 'administrativeArea',
                ],
                'postal_code_pattern' => '\d{5}',
                'postal_code_example' => '31001',
            ],
            'IR' => [
                'format' => "%organization\n%givenName %familyName\n%administrativeArea\n%locality, %dependentLocality\n%addressLine1\n%addressLine2\n%postalCode",
                'dependent_locality_type' => 'neighborhood',
                'postal_code_pattern' => '\d{5}-?\d{5}',
                'postal_code_example' => '11936-12345',
                'subdivision_depth' => 1,
            ],
            'IS' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '\d{3}',
                'postal_code_example' => '320',
            ],
            'IT' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality %administrativeArea",
                'required_fields' => [
                    'addressLine1', 'locality', 'administrativeArea', 'postalCode',
                ],
                'uppercase_fields' => [
                    'locality', 'administrativeArea',
                ],
                'postal_code_pattern' => '\d{5}',
                'postal_code_example' => '00144',
                'subdivision_depth' => 1,
            ],
            'JE' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality\nJERSEY\n%postalCode",
                'required_fields' => [
                    'addressLine1', 'locality', 'postalCode',
                ],
                'uppercase_fields' => [
                    'locality', 'postalCode',
                ],
                'postal_code_pattern' => 'JE\d[\dA-Z]? ?\d[ABD-HJLN-UW-Z]{2}',
                'postal_code_example' => 'JE1 1AA',
            ],
            'JM' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality\n%administrativeArea %sortingCode",
                'required_fields' => [
                    'addressLine1', 'locality', 'administrativeArea',
                ],
                'administrative_area_type' => 'parish',
                'subdivision_depth' => 1,
            ],
            'JO' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality %postalCode",
                'postal_code_pattern' => '\d{5}',
                'postal_code_example' => '11937',
            ],
            'JP' => [
                'locale' => 'ja',
                'format' => "%familyName %givenName\n%organization\n%addressLine1\n%addressLine2\n%locality, %administrativeArea\n%postalCode",
                'local_format' => "〒%postalCode\n%administrativeArea%locality\n%addressLine1\n%addressLine2\n%organization\n%familyName %givenName",
                'required_fields' => [
                    'addressLine1', 'locality', 'administrativeArea', 'postalCode',
                ],
                'uppercase_fields' => [
                    'administrativeArea',
                ],
                'administrative_area_type' => 'prefecture',
                'postal_code_pattern' => '\d{3}-?\d{4}',
                'postal_code_example' => '154-0023',
                'subdivision_depth' => 1,
            ],
            'KE' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality\n%postalCode",
                'postal_code_pattern' => '\d{5}',
                'postal_code_example' => '20100',
            ],
            'KG' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '\d{6}',
                'postal_code_example' => '720001',
            ],
            'KH' => [
                'format' => "%familyName %givenName\n%organization\n%addressLine1\n%addressLine2\n%locality %postalCode",
                'postal_code_pattern' => '\d{5}',
                'postal_code_example' => '12203',
            ],
            'KI' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%administrativeArea\n%locality",
                'uppercase_fields' => [
                    'addressLine1', 'addressLine2', 'locality', 'familyName', 'additionalName', 'givenName', 'organization', 'administrativeArea',
                ],
                'administrative_area_type' => 'island',
            ],
            'KM' => [
                'uppercase_fields' => [
                    'addressLine1', 'addressLine2', 'locality',
                ],
            ],
            'KN' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality, %administrativeArea",
                'required_fields' => [
                    'addressLine1', 'locality', 'administrativeArea',
                ],
                'administrative_area_type' => 'island',
                'subdivision_depth' => 1,
            ],
            'KP' => [
                'locale' => 'ko',
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality\n%administrativeArea, %postalCode",
                'local_format' => "%postalCode\n%administrativeArea\n%locality\n%addressLine1\n%addressLine2\n%organization\n%givenName %familyName",
                'subdivision_depth' => 1,
            ],
            'KR' => [
                'locale' => 'ko',
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%dependentLocality\n%locality\n%administrativeArea\n%postalCode",
                'local_format' => "%administrativeArea %locality%dependentLocality\n%addressLine1\n%addressLine2\n%organization\n%givenName %familyName\n%postalCode",
                'required_fields' => [
                    'addressLine1', 'locality', 'administrativeArea', 'postalCode',
                ],
                'uppercase_fields' => [
                    'postalCode',
                ],
                'administrative_area_type' => 'do_si',
                'dependent_locality_type' => 'district',
                'postal_code_pattern' => '\d{5}',
                'postal_code_example' => '03051',
                'subdivision_depth' => 3,
            ],
            'KW' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '\d{5}',
                'postal_code_example' => '54541',
            ],
            'KY' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%administrativeArea %postalCode",
                'required_fields' => [
                    'addressLine1', 'administrativeArea',
                ],
                'administrative_area_type' => 'island',
                'postal_code_pattern' => 'KY\d-\d{4}',
                'postal_code_example' => 'KY1-1100',
                'subdivision_depth' => 1,
            ],
            'KZ' => [
                'format' => "%postalCode\n%administrativeArea\n%locality\n%addressLine1\n%addressLine2\n%organization\n%givenName %familyName",
                'postal_code_pattern' => '\d{6}',
                'postal_code_example' => '040900',
            ],
            'LA' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '\d{5}',
                'postal_code_example' => '01160',
            ],
            'LB' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality %postalCode",
                'postal_code_pattern' => '(?:\d{4})(?: ?(?:\d{4}))?',
                'postal_code_example' => '2038 3054',
            ],
            'LI' => [
                'format' => "%organization\n%givenName %familyName\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'required_fields' => [
                    'addressLine1', 'locality', 'postalCode',
                ],
                'postal_code_pattern' => '948[5-9]|949[0-8]',
                'postal_code_example' => '9496',
                'postal_code_prefix' => 'FL-',
            ],
            'LK' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality\n%postalCode",
                'postal_code_pattern' => '\d{5}',
                'postal_code_example' => '20000',
            ],
            'LR' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '\d{4}',
                'postal_code_example' => '1000',
            ],
            'LS' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality %postalCode",
                'postal_code_pattern' => '\d{3}',
                'postal_code_example' => '100',
            ],
            'LT' => [
                'format' => "%organization\n%givenName %familyName\n%addressLine1\n%addressLine2\n%postalCode %locality %administrativeArea",
                'required_fields' => [
                    'addressLine1', 'locality', 'postalCode',
                ],
                'administrative_area_type' => 'county',
                'postal_code_pattern' => '\d{5}',
                'postal_code_example' => '04340',
                'postal_code_prefix' => 'LT-',
            ],
            'LU' => [
                'format' => "%organization\n%givenName %familyName\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'required_fields' => [
                    'addressLine1', 'locality', 'postalCode',
                ],
                'postal_code_pattern' => '\d{4}',
                'postal_code_example' => '4750',
                'postal_code_prefix' => 'L-',
            ],
            'LV' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality, %postalCode",
                'required_fields' => [
                    'addressLine1', 'locality', 'postalCode',
                ],
                'postal_code_pattern' => 'LV-\d{4}',
                'postal_code_example' => 'LV-1073',
            ],
            'MA' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '\d{5}',
                'postal_code_example' => '53000',
            ],
            'MC' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality %sortingCode",
                'postal_code_pattern' => '980\d{2}',
                'postal_code_example' => '98000',
                'postal_code_prefix' => 'MC-',
            ],
            'MD' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '\d{4}',
                'postal_code_example' => '2012',
                'postal_code_prefix' => 'MD-',
            ],
            'ME' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '8\d{4}',
                'postal_code_example' => '81257',
            ],
            'MF' => [
                'format' => "%organization\n%givenName %familyName\n%addressLine1\n%addressLine2\n%postalCode %locality %sortingCode",
                'required_fields' => [
                    'addressLine1', 'locality', 'postalCode',
                ],
                'uppercase_fields' => [
                    'addressLine1', 'addressLine2', 'locality', 'sortingCode',
                ],
                'postal_code_pattern' => '9[78][01]\d{2}',
                'postal_code_example' => '97100',
            ],
            'MG' => [
                'format' => "%familyName %givenName\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '\d{3}',
                'postal_code_example' => '501',
            ],
            'MH' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality %administrativeArea %postalCode",
                'required_fields' => [
                    'addressLine1', 'locality', 'administrativeArea', 'postalCode',
                ],
                'uppercase_fields' => [
                    'addressLine1', 'addressLine2', 'locality', 'familyName', 'additionalName', 'givenName', 'organization', 'administrativeArea',
                ],
                'administrative_area_type' => 'state',
                'postal_code_type' => 'zip',
                'postal_code_pattern' => '(969[67]\d)(?:[ \-](\d{4}))?',
                'postal_code_example' => '96960',
            ],
            'MK' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '\d{4}',
                'postal_code_example' => '1314',
            ],
            'MM' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality, %postalCode",
                'postal_code_pattern' => '\d{5}',
                'postal_code_example' => '11181',
            ],
            'MN' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality\n%administrativeArea %postalCode",
                'postal_code_pattern' => '\d{5}',
                'postal_code_example' => '65030',
            ],
            'MO' => [
                'locale' => 'zh-Hans',
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2",
                'local_format' => "%addressLine1\n%addressLine2\n%organization\n%givenName %familyName",
                'required_fields' => [
                    'addressLine1',
                ],
            ],
            'MP' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality %administrativeArea %postalCode",
                'required_fields' => [
                    'addressLine1', 'locality', 'administrativeArea', 'postalCode',
                ],
                'uppercase_fields' => [
                    'addressLine1', 'addressLine2', 'locality', 'familyName', 'additionalName', 'givenName', 'organization', 'administrativeArea',
                ],
                'administrative_area_type' => 'state',
                'postal_code_type' => 'zip',
                'postal_code_pattern' => '(9695[012])(?:[ \-](\d{4}))?',
                'postal_code_example' => '96950',
            ],
            'MQ' => [
                'format' => "%organization\n%givenName %familyName\n%addressLine1\n%addressLine2\n%postalCode %locality %sortingCode",
                'required_fields' => [
                    'addressLine1', 'locality', 'postalCode',
                ],
                'uppercase_fields' => [
                    'addressLine1', 'addressLine2', 'locality', 'sortingCode',
                ],
                'postal_code_pattern' => '9[78]2\d{2}',
                'postal_code_example' => '97220',
            ],
            'MR' => [
                'uppercase_fields' => [
                    'addressLine1', 'addressLine2', 'locality',
                ],
            ],
            'MT' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality %postalCode",
                'uppercase_fields' => [
                    'locality', 'postalCode',
                ],
                'postal_code_pattern' => '[A-Z]{3} ?\d{2,4}',
                'postal_code_example' => 'NXR 01',
            ],
            'MU' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%postalCode\n%locality",
                'uppercase_fields' => [
                    'locality', 'postalCode',
                ],
                'postal_code_pattern' => '\d{3}(?:\d{2}|[A-Z]{2}\d{3})',
                'postal_code_example' => '42602',
            ],
            'MV' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality %postalCode",
                'postal_code_pattern' => '\d{5}',
                'postal_code_example' => '20026',
            ],
            'MW' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality %sortingCode",
            ],
            'MX' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%dependentLocality\n%postalCode %locality, %administrativeArea",
                'required_fields' => [
                    'addressLine1', 'locality', 'postalCode',
                ],
                'uppercase_fields' => [
                    'locality', 'administrativeArea', 'postalCode',
                ],
                'administrative_area_type' => 'state',
                'dependent_locality_type' => 'neighborhood',
                'postal_code_pattern' => '\d{5}',
                'postal_code_example' => '02860',
                'subdivision_depth' => 1,
            ],
            'MY' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%dependentLocality\n%postalCode %locality\n%administrativeArea",
                'required_fields' => [
                    'addressLine1', 'locality', 'postalCode',
                ],
                'uppercase_fields' => [
                    'locality', 'administrativeArea',
                ],
                'administrative_area_type' => 'state',
                'dependent_locality_type' => 'village_township',
                'postal_code_pattern' => '\d{5}',
                'postal_code_example' => '43000',
                'subdivision_depth' => 1,
            ],
            'MZ' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality%administrativeArea",
                'postal_code_pattern' => '\d{4}',
                'postal_code_example' => '1102',
                'subdivision_depth' => 1,
            ],
            'NA' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%localityn%postalCode",
                'postal_code_pattern' => '\d{5}',
            ],
            'NC' => [
                'format' => "%organization\n%givenName %familyName\n%addressLine1\n%addressLine2\n%postalCode %locality %sortingCode",
                'required_fields' => [
                    'addressLine1', 'locality', 'postalCode',
                ],
                'uppercase_fields' => [
                    'addressLine1', 'addressLine2', 'locality', 'sortingCode',
                ],
                'postal_code_pattern' => '988\d{2}',
                'postal_code_example' => '98814',
            ],
            'NE' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '\d{4}',
                'postal_code_example' => '8001',
            ],
            'NF' => [
                'format' => "%organization\n%givenName %familyName\n%addressLine1\n%addressLine2\n%locality %administrativeArea %postalCode",
                'uppercase_fields' => [
                    'locality', 'administrativeArea',
                ],
                'postal_code_pattern' => '2899',
                'postal_code_example' => '2899',
            ],
            'NG' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%dependentLocality\n%locality %postalCode\n%administrativeArea",
                'uppercase_fields' => [
                    'locality', 'administrativeArea',
                ],
                'administrative_area_type' => 'state',
                'postal_code_pattern' => '\d{6}',
                'postal_code_example' => '930283',
                'subdivision_depth' => 1,
            ],
            'NI' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%postalCode\n%locality, %administrativeArea",
                'uppercase_fields' => [
                    'locality', 'administrativeArea',
                ],
                'administrative_area_type' => 'department',
                'postal_code_pattern' => '\d{5}',
                'postal_code_example' => '52000',
                'subdivision_depth' => 1,
            ],
            'NL' => [
                'format' => "%organization\n%givenName %familyName\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'required_fields' => [
                    'addressLine1', 'locality', 'postalCode',
                ],
                'postal_code_pattern' => '\d{4} ?[A-Z]{2}',
                'postal_code_example' => '1234 AB',
            ],
            'NO' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'required_fields' => [
                    'addressLine1', 'locality', 'postalCode',
                ],
                'locality_type' => 'post_town',
                'postal_code_pattern' => '\d{4}',
                'postal_code_example' => '0025',
            ],
            'NP' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality %postalCode",
                'postal_code_pattern' => '\d{5}',
                'postal_code_example' => '44601',
            ],
            'NR' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%administrativeArea",
                'required_fields' => [
                    'addressLine1', 'administrativeArea',
                ],
                'administrative_area_type' => 'district',
                'subdivision_depth' => 1,
            ],
            'NZ' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%dependentLocality\n%locality %postalCode",
                'required_fields' => [
                    'addressLine1', 'locality', 'postalCode',
                ],
                'postal_code_pattern' => '\d{4}',
                'postal_code_example' => '6001',
            ],
            'OM' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%postalCode\n%locality",
                'postal_code_pattern' => '(?:PC )?\d{3}',
                'postal_code_example' => '133',
            ],
            'PA' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality\n%administrativeArea",
                'uppercase_fields' => [
                    'locality', 'administrativeArea',
                ],
            ],
            'PE' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality %postalCode\n%administrativeArea",
                'locality_type' => 'district',
                'postal_code_pattern' => '(?:LIMA \d{1,2}|CALLAO 0?\d)|[0-2]\d{4}',
                'postal_code_example' => 'LIMA 23',
                'subdivision_depth' => 1,
            ],
            'PF' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality %administrativeArea",
                'required_fields' => [
                    'addressLine1', 'locality', 'administrativeArea', 'postalCode',
                ],
                'uppercase_fields' => [
                    'locality', 'administrativeArea',
                ],
                'administrative_area_type' => 'island',
                'postal_code_pattern' => '987\d{2}',
                'postal_code_example' => '98709',
            ],
            'PG' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality %postalCode %administrativeArea",
                'required_fields' => [
                    'addressLine1', 'locality', 'administrativeArea',
                ],
                'postal_code_pattern' => '\d{3}',
                'postal_code_example' => '111',
            ],
            'PH' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%dependentLocality, %locality\n%postalCode %administrativeArea",
                'postal_code_pattern' => '\d{4}',
                'postal_code_example' => '1008',
                'subdivision_depth' => 1,
            ],
            'PK' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality-%postalCode",
                'postal_code_pattern' => '\d{5}',
                'postal_code_example' => '44000',
            ],
            'PL' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'required_fields' => [
                    'addressLine1', 'locality', 'postalCode',
                ],
                'postal_code_pattern' => '\d{2}-\d{3}',
                'postal_code_example' => '00-950',
            ],
            'PM' => [
                'format' => "%organization\n%givenName %familyName\n%addressLine1\n%addressLine2\n%postalCode %locality %sortingCode",
                'required_fields' => [
                    'addressLine1', 'locality', 'postalCode',
                ],
                'uppercase_fields' => [
                    'addressLine1', 'addressLine2', 'locality', 'sortingCode',
                ],
                'postal_code_pattern' => '9[78]5\d{2}',
                'postal_code_example' => '97500',
            ],
            'PN' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality\n%postalCode",
                'required_fields' => [
                    'addressLine1', 'locality', 'postalCode',
                ],
                'uppercase_fields' => [
                    'locality', 'postalCode',
                ],
                'postal_code_pattern' => 'PCRN 1ZZ',
                'postal_code_example' => 'PCRN 1ZZ',
            ],
            'PR' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality %postalCode",
                'required_fields' => [
                    'addressLine1', 'locality', 'postalCode',
                ],
                'uppercase_fields' => [
                    'addressLine1', 'addressLine2', 'locality', 'familyName', 'additionalName', 'givenName', 'organization',
                ],
                'postal_code_type' => 'zip',
                'postal_code_pattern' => '(00[679]\d{2})(?:[ \-](\d{4}))?',
                'postal_code_example' => '00930',
                'postal_code_prefix' => 'PR ',
            ],
            'PT' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'required_fields' => [
                    'addressLine1', 'locality', 'postalCode',
                ],
                'postal_code_pattern' => '\d{4}-\d{3}',
                'postal_code_example' => '2725-079',
            ],
            'PW' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality %administrativeArea %postalCode",
                'required_fields' => [
                    'addressLine1', 'locality', 'administrativeArea', 'postalCode',
                ],
                'uppercase_fields' => [
                    'addressLine1', 'addressLine2', 'locality', 'familyName', 'additionalName', 'givenName', 'organization', 'administrativeArea',
                ],
                'administrative_area_type' => 'state',
                'postal_code_type' => 'zip',
                'postal_code_pattern' => '(969(?:39|40))(?:[ \-](\d{4}))?',
                'postal_code_example' => '96940',
            ],
            'PY' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '\d{4}',
                'postal_code_example' => '1536',
            ],
            'QA' => [
                'uppercase_fields' => [
                    'addressLine1', 'addressLine2', 'locality',
                ],
            ],
            'RE' => [
                'format' => "%organization\n%givenName %familyName\n%addressLine1\n%addressLine2\n%postalCode %locality %sortingCode",
                'required_fields' => [
                    'addressLine1', 'locality', 'postalCode',
                ],
                'uppercase_fields' => [
                    'addressLine1', 'addressLine2', 'locality', 'sortingCode',
                ],
                'postal_code_pattern' => '9[78]4\d{2}',
                'postal_code_example' => '97400',
            ],
            'RO' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'required_fields' => [
                    'addressLine1', 'locality', 'postalCode',
                ],
                'uppercase_fields' => [
                    'addressLine1', 'addressLine2', 'locality',
                ],
                'postal_code_pattern' => '\d{6}',
                'postal_code_example' => '060274',
            ],
            'RS' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '\d{5,6}',
                'postal_code_example' => '106314',
            ],
            'RU' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality\n%administrativeArea\n%postalCode",
                'required_fields' => [
                    'addressLine1', 'locality', 'administrativeArea', 'postalCode',
                ],
                'uppercase_fields' => [
                    'addressLine1', 'addressLine2', 'locality',
                ],
                'administrative_area_type' => 'oblast',
                'postal_code_pattern' => '\d{6}',
                'postal_code_example' => '247112',
                'subdivision_depth' => 1,
            ],
            'RW' => [
                'uppercase_fields' => [
                    'addressLine1', 'addressLine2', 'locality',
                ],
            ],
            'SA' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality %postalCode",
                'postal_code_pattern' => '\d{5}',
                'postal_code_example' => '11564',
            ],
            'SC' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality\n%administrativeArea",
                'uppercase_fields' => [
                    'administrativeArea',
                ],
                'administrative_area_type' => 'island',
            ],
            'SD' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality\n%postalCode",
                'locality_type' => 'district',
                'postal_code_pattern' => '\d{5}',
                'postal_code_example' => '11042',
            ],
            'SE' => [
                'format' => "%organization\n%givenName %familyName\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'required_fields' => [
                    'addressLine1', 'locality', 'postalCode',
                ],
                'locality_type' => 'post_town',
                'postal_code_pattern' => '\d{3} ?\d{2}',
                'postal_code_example' => '11455',
                'postal_code_prefix' => 'SE-',
            ],
            'SG' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\nSINGAPORE %postalCode",
                'required_fields' => [
                    'addressLine1', 'postalCode',
                ],
                'postal_code_pattern' => '\d{6}',
                'postal_code_example' => '546080',
            ],
            'SH' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality\n%postalCode",
                'required_fields' => [
                    'addressLine1', 'locality', 'postalCode',
                ],
                'uppercase_fields' => [
                    'locality', 'postalCode',
                ],
                'postal_code_pattern' => '(?:ASCN|STHL) 1ZZ',
                'postal_code_example' => 'STHL 1ZZ',
            ],
            'SI' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '\d{4}',
                'postal_code_example' => '4000',
                'postal_code_prefix' => 'SI-',
            ],
            'SJ' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'required_fields' => [
                    'addressLine1', 'locality', 'postalCode',
                ],
                'locality_type' => 'post_town',
                'postal_code_pattern' => '\d{4}',
                'postal_code_example' => '9170',
            ],
            'SK' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'required_fields' => [
                    'addressLine1', 'locality', 'postalCode',
                ],
                'postal_code_pattern' => '\d{3} ?\d{2}',
                'postal_code_example' => '010 01',
            ],
            'SM' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'required_fields' => [
                    'addressLine1', 'postalCode',
                ],
                'postal_code_pattern' => '4789\d',
                'postal_code_example' => '47890',
            ],
            'SN' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '\d{5}',
                'postal_code_example' => '12500',
            ],
            'SO' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality, %administrativeArea %postalCode",
                'required_fields' => [
                    'addressLine1', 'locality', 'administrativeArea',
                ],
                'uppercase_fields' => [
                    'addressLine1', 'addressLine2', 'locality', 'administrativeArea',
                ],
                'postal_code_pattern' => '[A-Z]{2} ?\d{5}',
                'postal_code_example' => 'JH 09010',
                'subdivision_depth' => 1,
            ],
            'SR' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality\n%administrativeArea",
                'uppercase_fields' => [
                    'addressLine1', 'addressLine2', 'administrativeArea',
                ],
                'subdivision_depth' => 1,
            ],
            'SV' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%postalCode-%locality\n%administrativeArea",
                'required_fields' => [
                    'addressLine1', 'locality', 'administrativeArea',
                ],
                'uppercase_fields' => [
                    'locality', 'administrativeArea', 'postalCode',
                ],
                'postal_code_pattern' => 'CP [1-3][1-7][0-2]\d',
                'postal_code_example' => 'CP 1101',
                'subdivision_depth' => 1,
            ],
            'SY' => [
                'locality_type' => 'district',
            ],
            'SZ' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality\n%postalCode",
                'uppercase_fields' => [
                    'addressLine1', 'addressLine2', 'locality', 'postalCode',
                ],
                'postal_code_pattern' => '[HLMS]\d{3}',
                'postal_code_example' => 'H100',
            ],
            'TA' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality\n%postalCode",
                'postal_code_pattern' => 'TDCU 1ZZ',
                'postal_code_example' => 'TDCU 1ZZ',
            ],
            'TC' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality\n%postalCode",
                'required_fields' => [
                    'addressLine1', 'locality', 'postalCode',
                ],
                'uppercase_fields' => [
                    'locality', 'postalCode',
                ],
                'postal_code_pattern' => 'TKCA 1ZZ',
                'postal_code_example' => 'TKCA 1ZZ',
            ],
            'TH' => [
                'locale' => 'th',
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%dependentLocality, %locality\n%administrativeArea %postalCode",
                'local_format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%dependentLocality %locality\n%administrativeArea %postalCode",
                'uppercase_fields' => [
                    'administrativeArea',
                ],
                'postal_code_pattern' => '\d{5}',
                'postal_code_example' => '10150',
                'subdivision_depth' => 1,
            ],
            'TJ' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '\d{6}',
                'postal_code_example' => '735450',
            ],
            'TM' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '\d{6}',
                'postal_code_example' => '744000',
            ],
            'TN' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '\d{4}',
                'postal_code_example' => '1002',
            ],
            'TR' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality/%administrativeArea",
                'required_fields' => [
                    'addressLine1', 'locality', 'postalCode',
                ],
                'locality_type' => 'district',
                'postal_code_pattern' => '\d{5}',
                'postal_code_example' => '01960',
                'subdivision_depth' => 1,
            ],
            'TV' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality\n%administrativeArea",
                'uppercase_fields' => [
                    'addressLine1', 'addressLine2', 'locality', 'administrativeArea',
                ],
                'administrative_area_type' => 'island',
                'subdivision_depth' => 1,
            ],
            'TW' => [
                'locale' => 'zh-Hant',
                'format' => "%familyName %givenName\n%organization\n%addressLine1\n%addressLine2\n%locality, %administrativeArea %postalCode",
                'local_format' => "%postalCode\n%administrativeArea%locality\n%addressLine1\n%addressLine2\n%organization\n%familyName %givenName",
                'required_fields' => [
                    'addressLine1', 'locality', 'administrativeArea', 'postalCode',
                ],
                'administrative_area_type' => 'county',
                'postal_code_pattern' => '\d{3}(?:\d{2})?',
                'postal_code_example' => '104',
                'subdivision_depth' => 2,
            ],
            'TZ' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '\d{4,5}',
                'postal_code_example' => '6090',
            ],
            'UA' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality\n%administrativeArea\n%postalCode",
                'required_fields' => [
                    'addressLine1', 'locality', 'administrativeArea', 'postalCode',
                ],
                'administrative_area_type' => 'oblast',
                'postal_code_pattern' => '\d{5}',
                'postal_code_example' => '15432',
                'subdivision_depth' => 1,
            ],
            'UM' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality %administrativeArea %postalCode",
                'required_fields' => [
                    'addressLine1', 'locality', 'administrativeArea',
                ],
                'uppercase_fields' => [
                    'addressLine1', 'addressLine2', 'locality', 'familyName', 'additionalName', 'givenName', 'organization', 'administrativeArea',
                ],
                'administrative_area_type' => 'state',
                'postal_code_type' => 'zip',
                'postal_code_pattern' => '96898',
                'postal_code_example' => '96898',
            ],
            'US' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality, %administrativeArea %postalCode",
                'required_fields' => [
                    'addressLine1', 'locality', 'administrativeArea', 'postalCode',
                ],
                'uppercase_fields' => [
                    'locality', 'administrativeArea',
                ],
                'administrative_area_type' => 'state',
                'postal_code_type' => 'zip',
                'postal_code_pattern' => '(\d{5})(?:[ \-](\d{4}))?',
                'postal_code_example' => '95014',
                'subdivision_depth' => 1,
            ],
            'UY' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality %administrativeArea",
                'uppercase_fields' => [
                    'locality', 'administrativeArea',
                ],
                'postal_code_pattern' => '\d{5}',
                'postal_code_example' => '11600',
                'subdivision_depth' => 1,
            ],
            'UZ' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality\n%administrativeArea",
                'uppercase_fields' => [
                    'locality', 'administrativeArea',
                ],
                'postal_code_pattern' => '\d{6}',
                'postal_code_example' => '702100',
            ],
            'VA' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '00120',
                'postal_code_example' => '00120',
            ],
            'VC' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality %postalCode",
                'postal_code_pattern' => 'VC\d{4}',
                'postal_code_example' => 'VC0100',
            ],
            'VE' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality %postalCode, %administrativeArea",
                'required_fields' => [
                    'addressLine1', 'locality', 'administrativeArea',
                ],
                'uppercase_fields' => [
                    'locality', 'administrativeArea',
                ],
                'administrative_area_type' => 'state',
                'postal_code_pattern' => '\d{4}',
                'postal_code_example' => '1010',
                'subdivision_depth' => 1,
            ],
            'VG' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality\n%postalCode",
                'required_fields' => [
                    'addressLine1',
                ],
                'postal_code_pattern' => 'VG\d{4}',
                'postal_code_example' => 'VG1110',
            ],
            'VI' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality %administrativeArea %postalCode",
                'required_fields' => [
                    'addressLine1', 'locality', 'administrativeArea', 'postalCode',
                ],
                'uppercase_fields' => [
                    'addressLine1', 'addressLine2', 'locality', 'familyName', 'additionalName', 'givenName', 'organization', 'administrativeArea',
                ],
                'administrative_area_type' => 'state',
                'postal_code_type' => 'zip',
                'postal_code_pattern' => '(008(?:(?:[0-4]\d)|(?:5[01])))(?:[ \-](\d{4}))?',
                'postal_code_example' => '00802-1222',
            ],
            'VN' => [
                'format' => "%familyName %givenName\n%organization\n%addressLine1\n%addressLine2\n%locality\n%administrativeArea %postalCode",
                'postal_code_pattern' => '\d{5}\d?',
                'postal_code_example' => '70010',
                'subdivision_depth' => 1,
            ],
            'WF' => [
                'format' => "%organization\n%givenName %familyName\n%addressLine1\n%addressLine2\n%postalCode %locality %sortingCode",
                'required_fields' => [
                    'addressLine1', 'locality', 'postalCode',
                ],
                'uppercase_fields' => [
                    'addressLine1', 'addressLine2', 'locality', 'sortingCode',
                ],
                'postal_code_pattern' => '986\d{2}',
                'postal_code_example' => '98600',
            ],
            'XK' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '[1-7]\d{4}',
                'postal_code_example' => '10000',
            ],
            'YT' => [
                'format' => "%organization\n%givenName %familyName\n%addressLine1\n%addressLine2\n%postalCode %locality %sortingCode",
                'required_fields' => [
                    'addressLine1', 'locality', 'postalCode',
                ],
                'uppercase_fields' => [
                    'addressLine1', 'addressLine2', 'locality', 'sortingCode',
                ],
                'postal_code_pattern' => '976\d{2}',
                'postal_code_example' => '97600',
            ],
            'ZA' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%dependentLocality\n%locality\n%postalCode",
                'required_fields' => [
                    'addressLine1', 'locality', 'postalCode',
                ],
                'postal_code_pattern' => '\d{4}',
                'postal_code_example' => '0083',
            ],
            'ZM' => [
                'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality",
                'postal_code_pattern' => '\d{5}',
                'postal_code_example' => '50100',
            ],
        ];
        // @codingStandardsIgnoreEnd

        return $definitions;
    }
}
