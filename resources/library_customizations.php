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
 */

/**
 * Returns the address format customizations for the provided country code.
 */
function get_address_format_customizations($countryCode) {
    $formatCustomizations = [];
    // Switch %organization and %recipient.
    // https://github.com/googlei18n/libaddressinput/issues/83
    $formatCustomizations['DE'] = [
        'format' => "%organization\n%recipient\n%addressLine1\n%addressLine2\n%postalCode %locality",
    ];
    // Make the postal codes required, add administrative area fields (EE, LT).
    // https://github.com/googlei18n/libaddressinput/issues/64
    $formatCustomizations['EE'] = [
        'format' => "%recipient\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality %administrativeArea",
        'required_fields' => [
            'recipient',
            'addressLine1',
            'locality',
            'postalCode',
        ],
        'administrative_area_type' => 'county',
    ];
    $formatCustomizations['LT'] = [
        'format' => "%organization\n%recipient\n%addressLine1\n%addressLine2\n%postalCode %locality %administrativeArea",
        'required_fields' => [
            'recipient',
            'addressLine1',
            'locality',
            'postalCode',
        ],
        'administrative_area_type' => 'county',
    ];
    $formatCustomizations['LV'] = [
        'required_fields' => [
            'recipient',
            'addressLine1',
            'locality',
            'postalCode',
        ],
    ];
    // Make the postal code required for CZ and SK.
    // https://github.com/googlei18n/libaddressinput/issues/88
    $formatCustomizations['CZ'] = [
        'required_fields' => [
            'recipient',
            'addressLine1',
            'locality',
            'postalCode',
        ],
    ];
    $formatCustomizations['SK'] = [
        'required_fields' => [
            'recipient',
            'addressLine1',
            'locality',
            'postalCode',
        ],
    ];

    return isset($formatCustomizations[$countryCode]) ? $formatCustomizations[$countryCode] : [];
}

/**
 * Returns the subdivision customizations for the provided parent id.
 */
function get_subdivision_customizations($parentId) {
    // Rename and reorder ES-PM.
    // https://github.com/googlei18n/libaddressinput/issues/48
    $subdivisionCustomizations['ES'] = [
        '_remove' => ['ES-PM'],
        '_add' => [
            // Add 'ES-PM' before 'ES-B'.
            'ES-PM' => 'ES-B',
        ],
        'ES-PM' => [
            'name' => 'Balears',
            'postal_code_pattern' => '07',
        ],
    ];
    // Rename and reorder MX-MEX.
    // https://github.com/googlei18n/libaddressinput/issues/49
    $subdivisionCustomizations['MX'] = [
        '_remove' => ['MX-MEX'],
        '_add' => [
            'MX-MEX' => 'MX-MIC',
        ],
        'MX-MEX' => [
            'code' => 'MEX',
            'name' => 'México',
            'postal_code_pattern' => '5[0-7]',
        ],
    ];
    // Rename three BS provinces.
    // https://github.com/googlei18n/libaddressinput/issues/51
    $subdivisionCustomizations['BS'] = [
        '_replace' => ['BS-fc2a25', 'BS-0adc5a', 'BS-EX'],
        'BS-fc2a25' => [
            'name' => 'Abaco',
        ],
        'BS-0adc5a' => [
            'name' => 'Andros',
        ],
        'BS-EX' => [
            'name' => 'Exuma',
        ],
    ];
    // IN-50c73a -> IN-TG, IN-UL -> IN-UT
    // https://github.com/googlei18n/libaddressinput/issues/54
    // https://github.com/googlei18n/libaddressinput/issues/59
    $subdivisionCustomizations['IN'] = [
        '_remove' => ['IN-50c73a', 'IN-UL'],
        '_add' => [
            'IN-TG' => 'IN-TR',
            'IN-UT' => 'IN-WB',
        ],
        'IN-TG' => [
            'name' => 'Telangana',
            'postal_code_pattern' => '5[0-3]',
        ],
        'IN-UT' => [
            'name' => 'Uttarakhand',
            'postal_code_pattern' => '24[46-9]|254|26[23]',
        ],
    ];
    // Remove Swiss administrative areas, they're not used for addressing.
    // https://github.com/googlei18n/libaddressinput/issues/89
    $subdivisionCustomizations['CH'] = [
        '_remove' => [
            'CH-AG', 'CH-AR', 'CH-AI', 'CH-BE', 'CH-BL', 'CH-BS', 'CH-FR',
            'CH-GE', 'CH-GL', 'CH-GR', 'CH-JU', 'CH-LU', 'CH-NE', 'CH-NW',
            'CH-OW', 'CH-SH', 'CH-SZ', 'CH-SO', 'CH-SG', 'CH-TI', 'CH-TG',
            'CH-UR', 'CH-VD', 'CH-VS', 'CH-ZG', 'CH-ZH',
        ],
    ];


    $subdivisionCustomizations['CO'] = [
        '_replace' => [
            'CO-DC',
            'CO-AMA',
            'CO-ANT',
            'CO-ARA',
            'CO-ATL',
            'CO-BOL',
            'CO-BOY',
            'CO-CAL',
            'CO-CAQ',
            'CO-CAS',
            'CO-CAU',
            'CO-CES',
            'CO-COR',
            'CO-CUN',
            'CO-CHO',
            'CO-GUA',
            'CO-GUV',
            'CO-HUI',
            'CO-LAG',
            'CO-MAG',
            'CO-MET',
            'CO-NAR',
            'CO-NSA',
            'CO-PUT',
            'CO-QUI',
            'CO-RIS',
            'CO-SAP',
            'CO-SAN',
            'CO-SUC',
            'CO-TOL',
            'CO-VAC',
            'CO-VAU',
            'CO-VID',
        ],
        'CO-DC' => [
            'code' => 'DC',
            'name' => 'Distrito Capital de Bogotá',
        ],
        'CO-AMA' => [
            'code' => 'AMA',
            'name' => 'Amazonas',
        ],
        'CO-ANT' => [
            'code' => 'ANT',
            'name' => 'Antioquia',
        ],
        'CO-ARA' => [
            'code' => 'ARA',
            'name' => 'Arauca',
        ],
        'CO-ATL' => [
            'code' => 'ATL',
            'name' => 'Atlántico',
        ],
        'CO-BOL' => [
            'code' => 'BOL',
            'name' => 'Bolívar',
        ],
        'CO-BOY' => [
            'code' => 'BOY',
            'name' => 'Boyacá',
        ],
        'CO-CAL' => [
            'code' => 'CAL',
            'name' => 'Caldas',
        ],
        'CO-CAQ' => [
            'code' => 'CAQ',
            'name' => 'Caquetá',
        ],
        'CO-CAS' => [
            'code' => 'CAS',
            'name' => 'Casanare',
        ],
        'CO-CAU' => [
            'code' => 'CAU',
            'name' => 'Cauca',
        ],
        'CO-CES' => [
            'code' => 'CES',
            'name' => 'Cesar',
        ],
        'CO-COR' => [
            'code' => 'COR',
            'name' => 'Córdoba',
        ],
        'CO-CUN' => [
            'code' => 'CUN',
            'name' => 'Cundinamarca',
        ],
        'CO-CHO' => [
            'code' => 'CHO',
            'name' => 'Chocó',
        ],
        'CO-GUA' => [
            'code' => 'GUA',
            'name' => 'Guainía',
        ],
        'CO-GUV' => [
            'code' => 'GUV',
            'name' => 'Guaviare',
        ],
        'CO-HUI' => [
            'code' => 'HUI',
            'name' => 'Huila',
        ],
        'CO-LAG' => [
            'code' => 'LAG',
            'name' => 'La Guajira',
        ],
        'CO-MAG' => [
            'code' => 'MAG',
            'name' => 'Magdalena',
        ],
        'CO-MET' => [
            'code' => 'MET',
            'name' => 'Meta',
        ],
        'CO-NAR' => [
            'code' => 'NAR',
            'name' => 'Nariño',
        ],
        'CO-NSA' => [
            'code' => 'NSA',
            'name' => 'Norte de Santander',
        ],
        'CO-PUT' => [
            'code' => 'PUT',
            'name' => 'Putumayo',
        ],
        'CO-QUI' => [
            'code' => 'QUI',
            'name' => 'Quindío',
        ],
        'CO-RIS' => [
            'code' => 'RIS',
            'name' => 'Risaralda',
        ],
        'CO-SAP' => [
            'code' => 'SAP',
            'name' => 'San Andrés, Providencia y Santa Catalina',
        ],
        'CO-SAN' => [
            'code' => 'SAN',
            'name' => 'Santander',
        ],
        'CO-SUC' => [
            'code' => 'SUC',
            'name' => 'Sucre',
        ],
        'CO-TOL' => [
            'code' => 'TOL',
            'name' => 'Tolima',
        ],
        'CO-VAC' => [
            'code' => 'VAC',
            'name' => 'Valle del Cauca',
        ],
        'CO-VAU' => [
            'code' => 'VAU',
            'name' => 'Vaupés',
        ],
        'CO-VID' => [
            'code' => 'VID',
            'name' => 'Vichada',
        ],
    ];

    return isset($subdivisionCustomizations[$parentId]) ? $subdivisionCustomizations[$parentId] : [];
}
