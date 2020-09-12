<?php

/**
 * Generates address formats, and the JSON files stored in resources/subdivision.
 */

set_time_limit(0);
date_default_timezone_set('UTC');

include __DIR__ . '/../vendor/autoload.php';
include __DIR__ . '/../resources/library_customizations.php';

use CommerceGuys\Addressing\AddressFormat\AddressField;
use CommerceGuys\Addressing\AddressFormat\AdministrativeAreaType;
use CommerceGuys\Addressing\AddressFormat\DependentLocalityType;
use CommerceGuys\Addressing\AddressFormat\PostalCodeType;
use CommerceGuys\Addressing\Country\CountryRepository;
use CommerceGuys\Addressing\Locale;

$countryRepository = new CountryRepository();
$countries = $countryRepository->getList();
ksort($countries);
$serviceUrl = 'https://chromium-i18n.appspot.com/ssl-address';

// Make sure we're starting from a clean slate.
if (is_dir(__DIR__ . '/subdivision')) {
    die('The subdivision/ directory must not exist.');
}

// Prepare the filesystem.
mkdir(__DIR__ . '/subdivision');

// Create a list of countries for which Google has definitions.
$foundCountries = ['ZZ'];
$index = file_get_contents($serviceUrl);
foreach ($countries as $countryCode => $countryName) {
    $link = "<a href='/ssl-address/data/{$countryCode}'>";
    // This is still faster than running a file_exists() for each country code.
    if (strpos($index, $link) !== false) {
        $foundCountries[] = $countryCode;
    }
}

echo "Converting the raw definitions into the expected format.\n";
$genericDefinition = null;
$addressFormats = [];
$groupedSubdivisions = [];
foreach ($foundCountries as $countryCode) {
    $definition = file_get_contents(__DIR__ . '/assets/google/' . $countryCode . '.json');
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
    $addressFormat = create_address_format_definition($countryCode, $definition);

    // Get the French subdivision names for Canada.
    // This mechanism can only work for countries with a single
    // alternative language and ISO-based subdivision codes
    // (URL example: data/CA/AB and data/CA/AB--fr).
    $languages = [];
    if ($countryCode == 'CA' && isset($definition['languages'])) {
        $languages = explode('~', $definition['languages']);
        array_shift($languages);
    }

    $subdivisionPaths = [];
    if (isset($definition['sub_keys'])) {
        $subdivisionKeys = explode('~', $definition['sub_keys']);
        foreach ($subdivisionKeys as $subdivisionKey) {
            $subdivisionPaths[] = $countryCode . '_' . $subdivisionKey;
        }
    }
    $groupedSubdivisions += generate_subdivisions($countryCode, [$countryCode], $subdivisionPaths, $languages);

    $addressFormats[$countryCode] = $addressFormat;
}

echo "Writing the final definitions to disk.\n";
// Subdivisions are stored in JSON.
foreach ($groupedSubdivisions as $parentId => $subdivisions) {
    file_put_json(__DIR__ . '/subdivision/' . $parentId . '.json', $subdivisions);
}
// Replace subdivision/ES.json with the old resources/subdivision/ES.json, to
// get around a dataset regression (https://github.com/googlei18n/libaddressinput/issues/160).
copy(__DIR__ . '/../resources/subdivision/ES.json', __DIR__ . '/subdivision/ES.json');
// Generate the subdivision depths for each country.
$depths = generate_subdivision_depths($foundCountries);
foreach ($depths as $countryCode => $depth) {
    $addressFormats[$countryCode]['subdivision_depth'] = $depth;
}
// Address formats are stored in PHP, then manually transferred to
// AddressFormatRepository.
file_put_php(__DIR__ . '/address_formats.php', $addressFormats);

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
 * Converts the provided data into php and writes it to the disk.
 */
function file_put_php($filename, $data)
{
    $data = var_export($data, true) . ';';
    // The var_export output is terrible, so try to get it as close as possible
    // to the final result.
    $array_keys = [
        '0 => ', '1 => ', '2 => ', '3 => ', '4 => ', '5 => ',
        '6 => ', '7 => ', '8 => ', '9 => ', '10 => ', '11 => ',
    ];
    $data = str_replace(['array (', "),\n", ');', "=> \n  "], ['[', "],\n", '];', '=> '], $data);
    $data = str_replace('=>   [', '=> [', $data);
    $data = str_replace($array_keys, '', $data);
    // Put fields into one row.
    $find = [];
    $replace = [];
    foreach (AddressField::getAll() as $field) {
        $find[] = "'$field',\n      '";
        $replace[] = "'$field', '";
    }
    $data = str_replace($find, $replace, $data);
    // Replace format single quotes with double quotes, to parse \n properly.
    $data = str_replace(["format' => '", ";;;'"], ['format\' => "', '"'], $data);
    // Reindent (from 2 to 4 spaces).
    $data = str_replace('  ', '    ', $data);
    // Unescape backslashes.
    $data = str_replace('\\\\', '\\', $data);
    $data = '<?php' . "\n\n" . '$data = ' . $data;
    file_put_contents($filename, $data);
}

/**
 * Recursively generates subdivision definitions.
 */
function generate_subdivisions($countryCode, array $parents, $subdivisionPaths, $languages)
{
    $group = build_group($parents);
    $subdivisions = [];
    $subdivisions[$group] = [
        'country_code' => $countryCode,
    ];
    if (count($parents) > 1) {
        // A single parent is the same as the country code, hence unnecessary.
        $subdivisions[$group]['parents'] = $parents;
    }

    foreach ($subdivisionPaths as $subdivisionPath) {
        $definition = file_get_contents(__DIR__ . '/assets/google/' . $subdivisionPath . '.json');
        $definition = json_decode($definition, true);
        // The lname is usable as a latin code when the key is non-latin.
        $code = $definition['key'];
        if (isset($definition['lname'])) {
            $code = $definition['lname'];
        }
        if (empty($subdivisions[$group]['locale']) && isset($definition['lang'], $definition['lname'])) {
            // Only add the locale if there's a local name.
            $subdivisions[$group]['locale'] = process_locale($definition['lang']);
        }
        // (Ab)use the local_name field to hold latin translations. This allows
        // us to support only a single translation, but since our only example
        // here is Canada (with French), it will do.
        $translationLanguage = reset($languages);
        if ($translationLanguage) {
            $translation = file_get_contents(__DIR__ . '/assets/google/' . $subdivisionPath . '--' . $translationLanguage . '.json');
            $translation = json_decode($translation, true);
            $subdivisions[$group]['locale'] = Locale::canonicalize($translationLanguage);
            $definition['lname'] = $definition['name'];
            $definition['name'] = $translation['name'];
        }
        // Remove the locale key if it wasn't filled.
        if (empty($subdivisions[$group]['locale'])) {
            unset($subdivisions[$group]['locale']);
        }
        // Generate the subdivision.
        $subdivisions[$group]['subdivisions'][$code] = create_subdivision_definition($countryCode, $code, $definition);

        if (isset($definition['sub_keys'])) {
            $subdivisions[$group]['subdivisions'][$code]['has_children'] = true;

            $subdivisionChildrenPaths = [];
            $subdivisionChildrenKeys = explode('~', $definition['sub_keys']);
            foreach ($subdivisionChildrenKeys as $subdivisionChildrenKey) {
                $subdivisionChildrenPaths[] = $subdivisionPath . '_' . $subdivisionChildrenKey;
            }

            $childParents = array_merge($parents, [$code]);
            $subdivisions += generate_subdivisions($countryCode, $childParents, $subdivisionChildrenPaths, $languages);
        }
    }
    // Apply any found customizations.
    $customizations = get_subdivision_customizations($group);
    $subdivisions[$group] = apply_subdivision_customizations($subdivisions[$group], $customizations);

    return !empty($subdivisions[$group]['subdivisions']) ? $subdivisions : [];
}

/**
 * Generates the subdivision depths for each country.
 */
function generate_subdivision_depths($countries)
{
    $depths = [];
    foreach ($countries as $countryCode) {
        $patterns = [
            __DIR__ . '/subdivision/' . $countryCode . '.json',
            __DIR__ . '/subdivision/' . $countryCode . '-*.json',
            __DIR__ . '/subdivision/' . $countryCode . '--*.json',
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
    // Avoid notices.
    $rawDefinition += [
        'lang' => null,
        'fmt' => null,
        'require' => null,
        'upper' => null,
        'state_name_type' => null,
        'locality_name_type' => null,
        'sublocality_name_type' => null,
        'zip_name_type' => null,
    ];
    // ZZ holds the defaults for all address formats, and these are missing.
    if ($countryCode == 'ZZ') {
        $rawDefinition['state_name_type'] = AdministrativeAreaType::getDefault();
        $rawDefinition['sublocality_name_type'] = DependentLocalityType::getDefault();
        $rawDefinition['zip_name_type'] = PostalCodeType::getDefault();
    }

    $addressFormat = [
        'locale' => process_locale($rawDefinition['lang']),
        'format' => null,
        'local_format' => null,
        'required_fields' => convert_fields($rawDefinition['require'], 'required'),
        'uppercase_fields' => convert_fields($rawDefinition['upper'], 'uppercase'),
    ];

    if (isset($rawDefinition['lfmt']) && $rawDefinition['lfmt'] != $rawDefinition['fmt']) {
        $addressFormat['format'] = convert_format($countryCode, $rawDefinition['lfmt']);
        $addressFormat['local_format'] = convert_format($countryCode, $rawDefinition['fmt']);
    } else {
        $addressFormat['format'] = convert_format($countryCode, $rawDefinition['fmt']);
        // We don't need the locale if there's no local format.
        unset($addressFormat['locale']);
    }

    $addressFormat['administrative_area_type'] = $rawDefinition['state_name_type'];
    $addressFormat['locality_type'] = $rawDefinition['locality_name_type'];
    $addressFormat['dependent_locality_type'] = $rawDefinition['sublocality_name_type'];
    $addressFormat['postal_code_type'] = $rawDefinition['zip_name_type'];
    if (isset($rawDefinition['zip'])) {
        $addressFormat['postal_code_pattern'] = $rawDefinition['zip'];
    }
    if (isset($rawDefinition['postprefix'])) {
        // Workaround for https://github.com/googlei18n/libaddressinput/issues/72.
        if ($rawDefinition['postprefix'] == 'PR') {
            $rawDefinition['postprefix'] = 'PR ';
        }

        $addressFormat['postal_code_prefix'] = $rawDefinition['postprefix'];
        // Remove the prefix from the format strings.
        // Workaround for https://github.com/googlei18n/libaddressinput/issues/71.
        $addressFormat['format'] = str_replace($addressFormat['postal_code_prefix'], '', $addressFormat['format']);
        $addressFormat['local_format'] = str_replace($addressFormat['postal_code_prefix'], '', $addressFormat['local_format']);
    }
    // Add the subdivision_depth to the end of the ZZ definition.
    if ($countryCode == 'ZZ') {
        $addressFormat['subdivision_depth'] = 0;
    }
    // Remove multiple spaces in the formats.
    if (!empty($addressFormat['format'])) {
        $addressFormat['format'] = preg_replace('/[[:blank:]]+/', ' ', $addressFormat['format']);
    }
    if (!empty($addressFormat['local_format'])) {
        $addressFormat['local_format'] = preg_replace('/[[:blank:]]+/', ' ', $addressFormat['local_format']);
    }

    // Apply any customizations.
    $customizations = get_address_format_customizations($countryCode);
    foreach ($customizations as $key => $values) {
        $addressFormat[$key] = $values;
    }
    // Denote the end of the format string for file_put_php().
    if (!empty($addressFormat['format'])) {
        $addressFormat['format'] .= ';;;';
    }
    if (!empty($addressFormat['local_format'])) {
        $addressFormat['local_format'] .= ';;;';
    }
    // Remove NULL keys.
    $addressFormat = array_filter($addressFormat, function ($value) {
        return !is_null($value);
    });
    // Remove empty local formats.
    if (empty($addressFormat['local_format'])) {
      unset($addressFormat['local_format']);
    }

    return $addressFormat;
}

/**
 * Creates a subdivision definition from Google's raw definition.
 */
function create_subdivision_definition($countryCode, $code, $rawDefinition)
{
    $subdivision = [];
    if (isset($rawDefinition['lname'])) {
        $subdivision['local_code'] = $rawDefinition['key'];
        if (isset($rawDefinition['name']) && $rawDefinition['key'] != $rawDefinition['name']) {
            $subdivision['local_name'] = $rawDefinition['name'];
        }
        if ($code != $rawDefinition['lname']) {
            $subdivision['name'] = $rawDefinition['lname'];
        }
    } elseif (isset($rawDefinition['name']) && $rawDefinition['key'] != $rawDefinition['name']) {
        $subdivision['name'] = $rawDefinition['name'];
    }
    if (isset($rawDefinition['isoid'])) {
        $subdivision['iso_code'] = $countryCode . '-' . $rawDefinition['isoid'];
    }
    if (isset($rawDefinition['xzip'])) {
        $subdivision['postal_code_pattern'] = $rawDefinition['xzip'];
        $subdivision['postal_code_pattern_type'] = 'full';
    } elseif (isset($rawDefinition['zip'])) {
        $subdivision['postal_code_pattern'] = $rawDefinition['zip'];
        // There are more than 12 000 subdivisions, but only a few Chinese
        // ones specify a full pattern. Therefore, the postal_code_pattern_type
        // value is the same for most subdivisions, and omitted to save space.
    }

    return $subdivision;
}

/**
 * Applies subdivision customizations.
 */
function apply_subdivision_customizations($subdivisions, $customizations)
{
    if (empty($customizations)) {
        return $subdivisions;
    }

    $customizations += [
        '_remove' => [],
        '_replace' => [],
        '_add' => [],
        '_add_after' => [],
    ];

    foreach ($customizations['_remove'] as $removeId) {
        unset($subdivisions['subdivisions'][$removeId]);
    }
    foreach ($customizations['_replace'] as $replaceId) {
        $subdivisions['subdivisions'][$replaceId] = $customizations[$replaceId];
    }
    foreach ($customizations['_add'] as $addId) {
        $subdivisions['subdivisions'][$addId] = $customizations[$addId];
    }
    foreach ($customizations['_add_after'] as $addId => $nextId) {
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
 * Processes the locale string.
 */
function process_locale($locale) {
    // Be more precise when it comes to Chinese Simplified.
    if ($locale == 'zh') {
        $locale = 'zh-hans';
    }
    return Locale::canonicalize($locale);
}

/**
 * Converts the provided format string into one recognized by the library.
 */
function convert_format($countryCode, $format)
{
    if (empty($format)) {
        return null;
    }
    // Expand the recipient token into separate familyName/givenName tokens.
    // The additionalName field is not used by default.
    // Hardcode the list of countries that write the family name before the
    // given name, since the API doesn't give us that info.
    $reverseCountries = [
        'KH', 'CN', 'HU', 'JP', 'KO', 'MG', 'TW', 'VN',
    ];
    if (in_array($countryCode, $reverseCountries)) {
        $format = str_replace('%N', '%N3 %N1', $format);
    } else {
        $format = str_replace('%N', '%N1 %N3', $format);
    }
    // Expand the address token into separate tokens for address lines 1 and 2.
    $format = str_replace('%A', '%1%n%2', $format);

    $replacements = [
        '%S' => '%' . AddressField::ADMINISTRATIVE_AREA,
        '%C' => '%' . AddressField::LOCALITY,
        '%D' => '%' . AddressField::DEPENDENT_LOCALITY,
        '%Z' => '%' . AddressField::POSTAL_CODE,
        '%X' => '%' . AddressField::SORTING_CODE,
        '%1' => '%' . AddressField::ADDRESS_LINE1,
        '%2' => '%' . AddressField::ADDRESS_LINE2,
        '%O' => '%' . AddressField::ORGANIZATION,
        '%N3' => '%' . AddressField::FAMILY_NAME,
        '%N2' => '%' . AddressField::ADDITIONAL_NAME,
        '%N1' => '%' . AddressField::GIVEN_NAME,
        '%n' => '\n',
        // Remove hardcoded strings which duplicate the country name.
        '%nÅLAND' => '',
        'JERSEY%n' => '',
        'GUERNSEY%n' => '',
        'GIBRALTAR%n' => '',
        'SINGAPORE ' => '',
    ];
    $format = strtr($format, $replacements);

    return $format;
}

/**
 * Converts google's field symbols to the expected values.
 */
function convert_fields($fields, $type)
{
    if (is_null($fields)) {
        return null;
    }
    if (empty($fields)) {
        return [];
    }

    // Expand the name token into separate tokens.
    if ($type == 'required') {
        // The additional name is never required.
        $fields = str_replace('N', '79', $fields);
    } else {
        $fields = str_replace('N', '789', $fields);
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
        '7' => AddressField::FAMILY_NAME,
        '8' => AddressField::ADDITIONAL_NAME,
        '9' => AddressField::GIVEN_NAME,
    ];

    $fields = str_split($fields);
    foreach ($fields as $key => $field) {
        if (isset($mapping[$field])) {
            $fields[$key] = $mapping[$field];
        }
    }

    return $fields;
}

/**
 * Copy of SubdivisionRepository::buildGroup().
 */
function build_group(array $parents)
{
    if (empty($parents)) {
        throw new \InvalidArgumentException('The $parents argument must not be empty.');
    }
    $countryCode = array_shift($parents);
    $group = $countryCode;
    if ($parents) {
        // A dash per key allows the depth to be guessed later.
        $group .= str_repeat('-', count($parents));
        // Hash the remaining keys to ensure that the group is ASCII safe.
        // crc32b is the fastest but has collisions due to its short length.
        // sha1 and md5 are forbidden by many projects and organizations.
        // This is the next fastest option.
        $group .= hash('tiger128,3', implode('-', $parents));
    }

    return $group;
}
