<?php

/**
 * Generates the JSON files stored in resources/address_format and resources/subdivision.
 */

set_time_limit(0);
date_default_timezone_set('UTC');

include '../vendor/autoload.php';
include '../resources/library_customizations.php';

use CommerceGuys\Addressing\Enum\AddressField;
use CommerceGuys\Addressing\Repository\CountryRepository;

// Make sure aria2 is installed.
exec('aria2c --version', $ariaVersion);
if (empty($ariaVersion) || strpos($ariaVersion[0], 'aria2 version') === false) {
    die('aria2 must be installed.');
}

// Prepare the filesystem.
$neededDirectories = ['address_format', 'subdivision', 'raw'];
foreach ($neededDirectories as $neededDirectory) {
    if (!is_dir($neededDirectory)) {
        mkdir($neededDirectory);
    }
}

$countryRepository = new CountryRepository();
$countries = $countryRepository->getList();
$serviceUrl = 'http://i18napis.appspot.com/address';

echo "Generating the url list.\n";

// Generate the url list for aria2.
$urlList = generate_url_list();
file_put_contents('raw/url_list.txt', $urlList);

// Invoke aria2 and fetch the data.
echo "Downloading the raw data from Google's endpoint.\n";
exec('cd raw && aria2c -u 16 -i url_list.txt');

// Create a list of countries for which Google has definitions.
$foundCountries = ['ZZ'];
$index = file_get_contents($serviceUrl);
foreach ($countries as $countryCode => $countryName) {
    $link = "<a href='/address/data/{$countryCode}'>";
    // This is still faster than running a file_exists() for each country code.
    if (strpos($index, $link) !== false) {
        $foundCountries[] = $countryCode;
    }
}

echo "Converting the raw definitions into the expected format.\n";

// Process the raw definitions and convert them into the expected format.
$genericDefinition = null;
$addressFormats = [];
$groupedSubdivisions = [];
foreach ($foundCountries as $countryCode) {
    $definition = file_get_contents('raw/' . $countryCode . '.json');
    $definition = json_decode($definition, true);
    $extraKeys = array_diff(array_keys($definition), ['id', 'key', 'name']);
    if (empty($extraKeys)) {
        // This is an empty definition, skip it.
        continue;
    }
    if ($countryCode == 'MO') {
        // Fix for Macao, which has latin and non-latin formats, but no lang.
        $definition['lang'] = 'zh';
    }

    if ($countryCode == 'ZZ') {
        // Save the ZZ definitions so that they can be used later.
        $genericDefinition = $definition;
    } else {
        // Merge-in the defaults from ZZ.
        $definition += $genericDefinition;
    }

    $addressFormat = create_address_format_definition($countryCode, $definition);

    // Create a list of available translations.
    // Ignore Hong Kong because the listed translation (English) is already
    // provided through the lname property.
    $languages = [];
    if (isset($definition['languages']) && $countryCode != 'HK') {
        $languages = explode('~', $definition['languages']);
        array_shift($languages);
    }

    if (isset($definition['sub_keys'])) {
        $subdivisionPaths = [];
        $subdivisionKeys = explode('~', $definition['sub_keys']);
        foreach ($subdivisionKeys as $subdivisionKey) {
            $subdivisionPaths[] = $countryCode . '_' . $subdivisionKey;
        }

        $groupedSubdivisions += generate_subdivisions($countryCode, $countryCode, $subdivisionPaths, $languages);
    }

    $addressFormats[$countryCode] = $addressFormat;
}

echo "Writing the final definitions to disk.\n";

// Write the new definitions to disk.
foreach ($addressFormats as $countryCode => $addressFormat) {
    file_put_json('address_format/' . $countryCode . '.json', $addressFormat);
}
foreach ($groupedSubdivisions as $parentId => $subdivisions) {
    file_put_json('subdivision/' . $parentId . '.json', $subdivisions);
}

// Generate the subdivision depths for each country.
$depths = generate_subdivision_depths($foundCountries);
file_put_json('subdivision/depths.json', $depths);

echo "Done.\n";

/**
 * Converts the provided data into json and writes it to the disk.
 */
function file_put_json($filename, $data)
{
    $data = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    // Indenting with tabs instead of 4 spaces gives us 20% smaller files.
    $data = str_replace('    ', "\t", $data);
    file_put_contents($filename, $data);
}

/**
 * Generates a list of all urls that need to be downloaded using aria2.
 */
function generate_url_list()
{
    global $serviceUrl;

    $index = file_get_contents($serviceUrl);
    // Get all links that start with /address/data.
    // This avoids the /address/examples urls which aren't needed.
    preg_match_all("/<a\shref=\'\/address\/data\/([^\"]*)\'>/siU", $index, $matches);
    // Assemble the urls
    $list = array_map(function ($href) use ($serviceUrl) {
        // Replace the url encoded single slash with a real one.
        $href = str_replace('&#39;', "'", $href);
        // Convert 'US/CA' into 'US_CA.json'.
        $filename = str_replace('/', '_', $href) . '.json';
        $url = $serviceUrl . '/data/' . $href;
        // aria2 expects the out= parameter to be in the next row,
        // indented by two spaces.
        $url .= "\n  out=$filename";

        return $url;
    }, $matches[1]);

    return implode("\n", $list);
}

/**
 * Recursively generates subdivision definitions.
 */
function generate_subdivisions($countryCode, $parentId, $subdivisionPaths, $languages)
{
    $subdivisions = [];
    // Start by retrieving all json definitions.
    $definitions = [];
    $definitionKeys = [];
    foreach ($subdivisionPaths as $subdivisionPath) {
        $definition = file_get_contents('raw/' . $subdivisionPath . '.json');
        $definition = json_decode($definition, true);

        $definitions[$subdivisionPath] = $definition;
        $definitionKeys[] = $definition['key'];
    }

    // Determine whether the definition keys are safe to be used as subdivision
    // ids (by having the same length, and being ASCII).
    $keySuitableAsId = true;
    foreach ($definitionKeys as $index => $key) {
        if (strlen($key) != strlen($definitionKeys[0]) || !ctype_print($key)) {
            $keySuitableAsId = false;
            break;
        }
    }

    foreach ($definitions as $subdivisionPath => $definition) {
        // Construct a safe id for this subdivision. Google doesn't have one.
        if (isset($definition['isoid'])) {
            // Administrative areas often have a numeric isoid.
            $subdivisionId = $parentId . '-' . $definition['isoid'];
        } elseif (count($definitionKeys) > 1 && $keySuitableAsId) {
            // Many administrative areas have no isoid, but use a safe
            // two/three letter identifier as the key.
            $subdivisionId = $parentId . '-' . $definition['key'];
        } elseif (in_array($countryCode, ['AU'])) {
            // Special case countries which have different key lengths,
            // but which are known to be safe (for example, Australia).
            $subdivisionId = $parentId . '-' . $definition['key'];
        } else {
            // Localities and dependent localities have keys that are
            // not guaranteed to be in the local script, so we hash them.
            $subdivisionId = $parentId . '-' . substr(sha1($parentId . $definition['key']), 0, 6);
        }
        if (!isset($subdivisions[$parentId])) {
            $subdivisions[$parentId] = [
                'country_code' => $countryCode,
                'parent_id' => ($countryCode == $parentId) ? null : $parentId,
                'locale' => determine_locale($definition),
            ];
        }
        $subdivisions[$parentId]['subdivisions'][$subdivisionId] = create_subdivision_definition($definition);

        // If the subdivision has translations, retrieve them.
        // Note: At the moment, only Canada and Switzerland have translations,
        // and those translations are for administrative areas only.
        // It is unknown whether the current way of assembling the url would
        // work with several levels of translated subdivisions.
        foreach ($languages as $language) {
            $translation = file_get_contents('raw/' . $subdivisionPath . '--' . $language . '.json');
            $translation = json_decode($translation, true);
            $subdivisions[$parentId]['subdivisions'][$subdivisionId]['translations'][$language]['name'] = $translation['name'];
        }

        if (isset($definition['sub_keys'])) {
            $subdivisions[$parentId]['subdivisions'][$subdivisionId]['has_children'] = true;

            $subdivisionChildrenPaths = [];
            $subdivisionChildrenKeys = explode('~', $definition['sub_keys']);
            foreach ($subdivisionChildrenKeys as $subdivisionChildrenKey) {
                $subdivisionChildrenPaths[] = $subdivisionPath . '_' . $subdivisionChildrenKey;
            }

            $subdivisions += generate_subdivisions($countryCode, $subdivisionId, $subdivisionChildrenPaths, $languages);
        }
    }

    // Apply any found customizations.
    $customizations = get_subdivision_customizations($parentId);
    $subdivisions[$parentId] = apply_subdivision_customizations($subdivisions[$parentId], $customizations);
    // All subdivisions have been removed. Remove the rest of the data.
    if (empty($subdivisions[$parentId]['subdivisions'])) {
        unset($subdivisions[$parentId]);
    }

    return $subdivisions;
}

/**
 * Generates the subdivision depths for each country.
 */
function generate_subdivision_depths($countries)
{
    $depths = [];
    foreach ($countries as $countryCode) {
        $patterns = [
            'subdivision/' . $countryCode . '.json',
            'subdivision/' . $countryCode . '-*.json',
            'subdivision/' . $countryCode . '-*-*.json',
        ];
        foreach ($patterns as $pattern) {
            if (glob($pattern)) {
                $previous = isset($depths[$countryCode]) ? $depths[$countryCode] : 0;
                $depths[$countryCode] = $previous + 1;
            } else {
                break;
            }
        }
    }

    return $depths;
}

/**
 * Creates an address format definition from Google's raw definition.
 */
function create_address_format_definition($countryCode, $rawDefinition)
{
    $addressFormat = [
        'locale' => determine_locale($rawDefinition),
        'format' => null,
        'required_fields' => convert_fields($rawDefinition['require'], 'required'),
        'uppercase_fields' => convert_fields($rawDefinition['upper'], 'uppercase'),
    ];
    // Make sure the recipient is always required by default.
    if (!in_array(AddressField::RECIPIENT, $addressFormat['required_fields'])) {
        $addressFormat['required_fields'] = array_merge([AddressField::RECIPIENT], $addressFormat['required_fields']);
    }

    $translations = [];
    if (isset($rawDefinition['lfmt']) && $rawDefinition['lfmt'] != $rawDefinition['fmt']) {
        // Handle the China/Korea/Japan dual formats via translations.
        $language = $rawDefinition['lang'];
        $translations[$language]['format'] = convert_format($rawDefinition['fmt']);
        $addressFormat['format'] = convert_format($rawDefinition['lfmt']);
    } else {
        $addressFormat['format'] = convert_format($rawDefinition['fmt']);
    }

    if (strpos($addressFormat['format'], '%' . AddressField::ADMINISTRATIVE_AREA) !== false) {
        $addressFormat['administrative_area_type'] = $rawDefinition['state_name_type'];
    }
    if (strpos($addressFormat['format'], '%' . AddressField::LOCALITY) !== false) {
        $addressFormat['locality_type'] = $rawDefinition['locality_name_type'];
    }
    if (strpos($addressFormat['format'], '%' . AddressField::DEPENDENT_LOCALITY) !== false) {
        $addressFormat['dependent_locality_type'] = $rawDefinition['sublocality_name_type'];
    }
    if (strpos($addressFormat['format'], '%' . AddressField::POSTAL_CODE) !== false) {
        $addressFormat['postal_code_type'] = $rawDefinition['zip_name_type'];
        if (isset($rawDefinition['zip'])) {
            $addressFormat['postal_code_pattern'] = $rawDefinition['zip'];
        }
        if (isset($rawDefinition['postprefix'])) {
            // Workaround for https://github.com/googlei18n/libaddressinput/issues/72.
            if ($rawDefinition['postprefix'] == 'PR') {
                $rawDefinition['postprefix'] = 'PR ';
            } elseif ($rawDefinition['postprefix'] == 'SI-') {
                $rawDefinition['postprefix'] = 'SI- ';
            }

            $addressFormat['postal_code_prefix'] = $rawDefinition['postprefix'];
            // Remove the prefix from the format strings.
            // Workaround for https://github.com/googlei18n/libaddressinput/issues/71.
            $addressFormat['format'] = str_replace($addressFormat['postal_code_prefix'], '', $addressFormat['format']);
            foreach ($translations as $language => $translation) {
                $translations[$language]['format'] = str_replace($addressFormat['postal_code_prefix'], '', $translation['format']);
            }
        }
    }

    // Add translations as the last key.
    if ($translations) {
        $addressFormat['translations'] = $translations;
    }
    // Apply any customizations.
    $customizations = get_address_format_customizations($countryCode);
    foreach ($customizations as $key => $values) {
        $addressFormat[$key] = $values;
    }

    return $addressFormat;
}

/**
 * Creates a subdivision definition from Google's raw definition.
 */
function create_subdivision_definition($rawDefinition)
{
    // The name property isn't set when it's the same as the key.
    if (!isset($rawDefinition['name'])) {
        $rawDefinition['name'] = $rawDefinition['key'];
    }

    $subdivision = [
        'code' => $rawDefinition['key'],
        'name' => $rawDefinition['name'],
    ];
    if (isset($rawDefinition['xzip'])) {
        $subdivision['postal_code_pattern'] = $rawDefinition['xzip'];
        $subdivision['postal_code_pattern_type'] = 'full';
    } elseif (isset($rawDefinition['zip'])) {
        $subdivision['postal_code_pattern'] = $rawDefinition['zip'];
        // There are more than 12 000 subdivisions, but only a few Chinese
        // ones specify a full pattern. Therefore, the postal_code_pattern_type
        // value is the same for most subdivisions, and omitted to save space.
    }
    if (isset($rawDefinition['lname'])) {
        // Handle the China/Korea/Japan dual names via translation.
        $language = $rawDefinition['lang'];
        $subdivision['translations'][$language]['name'] = $rawDefinition['name'];
        $subdivision['name'] = $rawDefinition['lname'];
    }
    if ($subdivision['code'] == $subdivision['name'] && empty($subdivision['translations'])) {
        // Remove the code if it matches the name, to save space.
        unset($subdivision['code']);
    }

    return $subdivision;
}

/**
 * Applies subdivision customizations.
 */
function apply_subdivision_customizations($subdivisions, $customizations) {
    if (empty($customizations)) {
        return $subdivisions;
    }

    $customizations += [
        '_remove' => [],
        '_replace' => [],
        '_add' => [],
    ];

    foreach ($customizations['_remove'] as $removeId) {
        unset($subdivisions['subdivisions'][$removeId]);
    }
    foreach ($customizations['_replace'] as $replaceId) {
        $subdivisions['subdivisions'][$replaceId] = $customizations[$replaceId];
    }
    foreach ($customizations['_add'] as $addId => $nextId) {
        $position = array_search($nextId, array_keys($subdivisions['subdivisions']));
        $new = [
            $addId => $customizations[$addId],
        ];
        // array_splice() doesn't support non-numeric replacement keys.
        $start = array_slice($subdivisions['subdivisions'], 0, $position);
        $end = array_slice($subdivisions['subdivisions'], $position);
        $subdivisions['subdivisions'] = $start + $new + $end;
    }

    return $subdivisions;
}

/**
 * Determines the correct locale of a definition.
 */
function determine_locale($rawDefinition)
{
    $locale = 'und';
    if (isset($rawDefinition['lang'])) {
        $locale = $rawDefinition['lang'];
        if (isset($rawDefinition['lfmt']) || isset($rawDefinition['lname'])) {
            // When the definition has separate latin-script properties,
            // they are taken as the default. The langcode needs to indicate
            // this. So, zh-Latn gets set for China, ja-Latn for Japan, etc.
            $localeParts = explode('-', $locale);
            $locale = $localeParts[0] . '-Latn';
        }
    }

    return $locale;
}

/**
 * Converts the provided format string into one recognized by the library.
 */
function convert_format($format)
{
    // Expand the address token into separate tokens for address lines 1 and 2.
    // Follow the direction of the fields.
    if (strpos($format, '%N') < strpos($format, '%A')) {
        $format = str_replace('%A', '%1%n%2', $format);
    } else {
        $format = str_replace('%A', '%2%n%1', $format);
    }

    $replacements = [
        '%S' => '%' . AddressField::ADMINISTRATIVE_AREA,
        '%C' => '%' . AddressField::LOCALITY,
        '%D' => '%' . AddressField::DEPENDENT_LOCALITY,
        '%Z' => '%' . AddressField::POSTAL_CODE,
        '%X' => '%' . AddressField::SORTING_CODE,
        '%1' => '%' . AddressField::ADDRESS_LINE1,
        '%2' => '%' . AddressField::ADDRESS_LINE2,
        '%O' => '%' . AddressField::ORGANIZATION,
        '%N' => '%' . AddressField::RECIPIENT,
        '%n' => "\n",
    ];

    return strtr($format, $replacements);
}

/**
 * Converts google's field symbols to the expected values.
 */
function convert_fields($fields, $type)
{
    if (empty($fields)) {
        return [];
    }

    // Expand the address token into separate tokens for address lines 1 and 2.
    // For required fields it's enough to require the first line.
    if ($type == 'required') {
        $fields = str_replace('A', '1', $fields);
    } else {
        $fields = str_replace('A', '12', $fields);
    }

    $mapping = [
        'S' => AddressField::ADMINISTRATIVE_AREA,
        'C' => AddressField::LOCALITY,
        'D' => AddressField::DEPENDENT_LOCALITY,
        'Z' => AddressField::POSTAL_CODE,
        'X' => AddressField::SORTING_CODE,
        '1' => AddressField::ADDRESS_LINE1,
        '2' => AddressField::ADDRESS_LINE2,
        'O' => AddressField::ORGANIZATION,
        'N' => AddressField::RECIPIENT,
    ];

    $fields = str_split($fields);
    foreach ($fields as $key => $field) {
        if (isset($mapping[$field])) {
            $fields[$key] = $mapping[$field];
        }
    }

    return $fields;
}
