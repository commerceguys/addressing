<?php

/**
 * A number of issues were found in Google's dataset and reported at.
 * https://github.com/googlei18n/libaddressinput/issues
 * Since Google has been slow to resolve them, the library maintains its own
 * list of customizations, in PHP format for easier contribution.
 *
 * @todo
 * PE subdivisions (https://github.com/googlei18n/libaddressinput/issues/50)
 * MZ https://github.com/googlei18n/libaddressinput/issues/58
 * Other points raised in https://github.com/googlei18n/libaddressinput/issues/49
 */

/**
 * Returns the address format customizations for the provided country code.
 */
function get_address_format_customizations($countryCode) {
    $formatCustomizations = [];
    // Switch %organization and %recipient.
    // https://github.com/googlei18n/libaddressinput/issues/83
    $formatCustomizations['DE'] = [
        'format' => '%organization\n%givenName %familyName\n%addressLine1\n%addressLine2\n%postalCode %locality',
    ];
    // Remove the administrative area.
    // https://github.com/googlei18n/libaddressinput/issues/115
    $formatCustomizations['GU'] = [
        'format' => '%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality %postalCode',
         'required_fields' => [
             'addressLine1',
             'locality',
             'postalCode',
         ],
    ];
    // Make the postal codes required, add administrative area fields (EE, LT).
    // https://github.com/googlei18n/libaddressinput/issues/64
    $formatCustomizations['EE'] = [
        'format' => '%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality %administrativeArea',
        'required_fields' => [
            'addressLine1',
            'locality',
            'postalCode',
        ],
        'administrative_area_type' => 'county',
    ];
    $formatCustomizations['LT'] = [
        'format' => '%organization\n%givenName %familyName\n%addressLine1\n%addressLine2\n%postalCode %locality %administrativeArea',
        'required_fields' => [
            'addressLine1',
            'locality',
            'postalCode',
        ],
        'administrative_area_type' => 'county',
    ];
    $formatCustomizations['LV'] = [
        'required_fields' => [
            'addressLine1',
            'locality',
            'postalCode',
        ],
    ];
    // Make the postal code required for CZ and SK.
    // https://github.com/googlei18n/libaddressinput/issues/88
    $formatCustomizations['CZ'] = [
        'required_fields' => [
            'addressLine1',
            'locality',
            'postalCode',
        ],
    ];
    $formatCustomizations['SK'] = [
        'required_fields' => [
            'addressLine1',
            'locality',
            'postalCode',
        ],
    ];

    return isset($formatCustomizations[$countryCode]) ? $formatCustomizations[$countryCode] : [];
}

/**
 * Returns the subdivision customizations for the provided group.
 */
function get_subdivision_customizations($group) {
    // 'Islas Baleares' -> 'Balears'.
    // https://github.com/googlei18n/libaddressinput/issues/48
    $subdivisionCustomizations['ES'] = [
        '_remove' => ['Islas Baleares'],
        '_add' => [
            // Add 'Balears' before 'Barcelona'.
            'Balears' => 'Barcelona',
        ],
        'Balears' => [
            'name' => 'Balears',
            'iso_code' => 'ES-PM',
            'postal_code_pattern' => '07',
        ],
    ];
    // 'Estado de México' => 'México'.
    // https://github.com/googlei18n/libaddressinput/issues/49
    $subdivisionCustomizations['MX'] = [
        '_remove' => ['MEX'],
        '_add' => [
            'MEX' => 'MIC',
        ],
        'MEX' => [
            'name' => 'México',
            'iso_code' => 'MX-MEX',
            'postal_code_pattern' => '5[0-7]',
        ],
    ];

    return isset($subdivisionCustomizations[$group]) ? $subdivisionCustomizations[$group] : [];
}
