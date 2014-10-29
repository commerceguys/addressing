<?php

/**
 * Generates the JSON files stored in resources/address_format and resources/subdivision.
 */

set_time_limit(0);

include '../vendor/autoload.php';

use CommerceGuys\Intl\Country\CountryRepository;
use CommerceGuys\Addressing\Model\AddressFormat;

$countryRepository = new CountryRepository();
$countries = $countryRepository->getAll();
$service_url = 'http://i18napis.appspot.com/address';
if (!is_dir('address_format')) {
    die('Could not find the empty address_format/ folder, please create it.');
}
if (!is_dir('subdivision')) {
    die('Could not find the empty subdivision/ folder, please create it.');
}

// Create a list of countries for which Google has definitions.
$foundCountries = array('ZZ');
$index = file_get_contents($service_url);
foreach ($countries as $country) {
    $countryCode = $country->getCountryCode();
    $link = "<a href='/address/data/{$countryCode}'>";
    if (strpos($index, $link) !== FALSE) {
        $foundCountries[] = $countryCode;
    }
}

$genericDefinition = null;
$regions = 0;
// Generate address format definitions.
foreach ($foundCountries as $countryCode) {
    $definition = file_get_contents($service_url . '/data/' . $countryCode);
    $definition = json_decode($definition, true);
    $extraKeys = array_diff(array_keys($definition), array('id', 'key', 'name'));
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

    $addressFormat = create_address_format_definition($definition);

    // Create a list of available translations.
    // Ignore Hong Kong because the listed translation (English) is already
    // provided through the lname property.
    $languages = array();
    if (isset($definition['languages']) && $countryCode != 'HK') {
        $languages = explode('~', $definition['languages']);
        array_shift($languages);
    }

    if (isset($definition['sub_keys'])) {
        $subdivisionPaths = array();
        $subdivisionKeys = explode('~', $definition['sub_keys']);
        foreach ($subdivisionKeys as $subdivisionKey) {
            $subdivisionPaths[] = $countryCode . '/' . rawurlencode($subdivisionKey);
        }

        generate_subdivisions($countryCode, $countryCode, $subdivisionPaths, $languages);
    }

    $addressFormat = json_encode($addressFormat, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    file_put_contents('address_format/' . $countryCode . '.json', $addressFormat);
}

/**
 * Recursively generates subdivision definitions.
 */
function generate_subdivisions($countryCode, $parentId, $subdivisionPaths, $languages)
{
    global $service_url;

    // Start by retrieving all json definitions.
    $definitions = array();
    $definitionKeys = array();
    foreach ($subdivisionPaths as $subdivisionPath) {
        $definition = file_get_contents($service_url . '/data/' . $subdivisionPath);
        $definition = json_decode($definition, true);

        $definitions[$subdivisionPath] = $definition;
        $definitionKeys[] = $definition['key'];
    }

    // Determine whether the definition keys are safe to be used as subdivision
    // ids (by having the same length, and being ASCII).
    $keySuitableForId = true;
    foreach ($definitionKeys as $index => $key) {
        if (strlen($key) != strlen($definitionKeys[0]) || !ctype_print($key)) {
            $keySuitableAsId = false;
            break;
        }
    }

    $subdivisions = array();
    foreach ($definitions as $subdivisionPath => $definition) {
        // Construct a safe id for this subdivision. Google doesn't have one.
        if (isset($definition['isoid'])) {
            // Administrative areas often have a numeric isoid.
            $subdivisionId = $parentId . '-' . $definition['isoid'];
        } elseif (count($definitionKeys) > 1 && $keySuitableAsId) {
            // Many administrative areas have no isoid, but use a safe
            // two/three letter identifier as the key.
            $subdivisionId = $parentId . '-' . $definition['key'];
        } elseif (in_array($countryCode, array('AU'))) {
            // Special case countries which have different key lengths,
            // but which are known to be safe (for example, Australia).
            $subdivisionId = $parentId . '-' . $definition['key'];
        } else {
            // Localities and dependent localities have keys that are
            // not guaranteed to be in the local script, so we hash them.
            $subdivisionId = $parentId . '-' . substr(sha1($parentId . $definition['key']), 0, 6);
        }
        $subdivisions[$subdivisionId] = create_subdivision_definition($countryCode, $parentId, $subdivisionId, $definition);

        // If the subdivision has translations, retrieve them.
        // Note: At the moment, only Canada and Switzerland have translations,
        // and those translations are for administrative areas only.
        // It is unknown whether the current way of assembling the url would
        // work with several levels of translated subdivisions.
        foreach ($languages as $language) {
            $translation = file_get_contents($service_url . '/data/' . $subdivisionPath . '--' . $language);
            $translation = json_decode($translation, true);
            $subdivisions[$subdivisionId]['translations'][$language]['name'] = $translation['name'];
        }

        if (isset($definition['sub_keys'])) {
            $subdivisions[$subdivisionId]['has_children'] = true;

            $subdivisionChildrenPaths = array();
            $subdivisionChildrenKeys = explode('~', $definition['sub_keys']);
            foreach ($subdivisionChildrenKeys as $subdivisionChildrenKey) {
                $subdivisionChildrenPaths[] = $subdivisionPath . '/' . rawurlencode($subdivisionChildrenKey);
            }

            generate_subdivisions($countryCode, $subdivisionId, $subdivisionChildrenPaths, $languages);
        }
    }

    $subdivisions = json_encode($subdivisions, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    file_put_contents('subdivision/' . $parentId . '.json', $subdivisions);
}

/**
 * Creates an address format definition from Google's raw definition.
 */
function create_address_format_definition($rawDefinition)
{
    $addressFormat = array(
        'locale' => determine_locale($rawDefinition),
        'format' => null,
    );
    if (isset($rawDefinition['require'])) {
        $required = str_split($rawDefinition['require']);
        $addressFormat['required_fields'] = convert_fields($required);
    }
    if (isset($rawDefinition['upper'])) {
        $uppercase = str_split($rawDefinition['upper']);
        $addressFormat['uppercase_fields'] = convert_fields($uppercase);
    }
    if (isset($rawDefinition['state_name_type'])) {
        $addressFormat['administrative_area_type'] = $rawDefinition['state_name_type'];
    }
    if (isset($rawDefinition['zip_name_type'])) {
        $addressFormat['postal_code_type'] = $rawDefinition['zip_name_type'];
    }
    if (isset($rawDefinition['zip'])) {
        $addressFormat['postal_code_pattern'] = $rawDefinition['zip'];
    }
    if (isset($rawDefinition['postprefix'])) {
        $addressFormat['postal_code_prefix'] = $rawDefinition['postprefix'];
    }

    if (isset($rawDefinition['lfmt'])) {
        // Handle the China/Korea/Japan dual formats via translation.
        $language = $rawDefinition['lang'];
        $addressFormat['translations'][$language]['format'] = convert_format($rawDefinition['fmt']);
        $addressFormat['format'] = convert_format($rawDefinition['lfmt']);
    } else {
        $addressFormat['format'] = convert_format($rawDefinition['fmt']);
    }

    return $addressFormat;
}

/**
 * Creates a subdivision definition from Google's raw definition.
 */
function create_subdivision_definition($countryCode, $parentId, $subdivisionId, $rawDefinition)
{
    // The name property isn't set when it's the same as the key.
    if (!isset($rawDefinition['name'])) {
        $rawDefinition['name'] = $rawDefinition['key'];
    }
    if ($countryCode == $parentId) {
        // This is the top of the hierarchy.
        $parentId = null;
    }

    $subdivision = array(
        'locale' => determine_locale($rawDefinition),
        'country_code' => $countryCode,
        'parent_id' => $parentId,
        'id' => $subdivisionId,
        'code' => $rawDefinition['key'],
        'name' => $rawDefinition['name'],
    );
    if (isset($rawDefinition['zip'])) {
        $subdivision['postal_code_pattern'] = $rawDefinition['zip'];
    }
    if (isset($rawDefinition['lname'])) {
        // Handle the China/Korea/Japan dual names via translation.
        $language = $rawDefinition['lang'];
        $subdivision['translations'][$language]['name'] = $rawDefinition['name'];
        $subdivision['name'] = $rawDefinition['lname'];
    }

    return $subdivision;
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
    $replacements = array(
        '%S' => '%' . AddressFormat::FIELD_ADMINISTRATIVE_AREA,
        '%C' => '%' . AddressFormat::FIELD_LOCALITY,
        '%D' => '%' . AddressFormat::FIELD_DEPENDENT_LOCALITY,
        '%Z' => '%' . AddressFormat::FIELD_POSTAL_CODE,
        '%X' => '%' . AddressFormat::FIELD_SORTING_CODE,
        '%A' => '%' . AddressFormat::FIELD_ADDRESS,
        '%O' => '%' . AddressFormat::FIELD_ORGANIZATION,
        '%N' => '%' . AddressFormat::FIELD_RECIPIENT,
        '%n' => "\n",
    );

    return strtr($format, $replacements);
}

/**
 * Converts google's field symbols to the expected values.
 */
function convert_fields(array $fields)
{
    $mapping = array(
        'S' => AddressFormat::FIELD_ADMINISTRATIVE_AREA,
        'C' => AddressFormat::FIELD_LOCALITY,
        'D' => AddressFormat::FIELD_DEPENDENT_LOCALITY,
        'Z' => AddressFormat::FIELD_POSTAL_CODE,
        'X' => AddressFormat::FIELD_SORTING_CODE,
        'A' => AddressFormat::FIELD_ADDRESS,
        'O' => AddressFormat::FIELD_ORGANIZATION,
        'N' => AddressFormat::FIELD_RECIPIENT,
    );

    foreach ($fields as $key => $field) {
        if (isset($mapping[$field])) {
            $fields[$key] = $mapping[$field];
        }
    }

    return $fields;
}
