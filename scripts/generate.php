<?php

/**
 * Generates the JSON files stored in resources/address_format and resources/subdivision.
 */

set_time_limit(0);
date_default_timezone_set('UTC');

include '../vendor/autoload.php';
include '../resources/library_customizations.php';

use CommerceGuys\Addressing\AddressFormat\AddressField;
use CommerceGuys\Addressing\AddressFormat\AdministrativeAreaType;
use CommerceGuys\Addressing\AddressFormat\DependentLocalityType;
use CommerceGuys\Addressing\AddressFormat\PostalCodeType;
use CommerceGuys\Addressing\Country\CountryRepository;
use CommerceGuys\Addressing\LocaleHelper;

// Make sure aria2 is installed.
exec('aria2c --version', $ariaVersion);
if (empty($ariaVersion) || strpos($ariaVersion[0], 'aria2 version') === false) {
    die('aria2 must be installed.');
}

// Prepare the filesystem.
$neededDirectories = ['subdivision', 'raw'];
foreach ($neededDirectories as $neededDirectory) {
    if (!is_dir($neededDirectory)) {
        mkdir($neededDirectory);
    }
}

$countryRepository = new CountryRepository();
$countries = $countryRepository->getList();
ksort($countries);
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

        $groupedSubdivisions += generate_subdivisions($countryCode, [$countryCode], $subdivisionPaths, $languages);
    }

    $addressFormats[$countryCode] = $addressFormat;
}

echo "Writing the final definitions to disk.\n";
// Subdivisions are stored in JSON.
foreach ($groupedSubdivisions as $parentId => $subdivisions) {
    file_put_json('subdivision/' . $parentId . '.json', $subdivisions);
}
// Generate the subdivision depths for each country.
$depths = generate_subdivision_depths($foundCountries);
foreach ($depths as $countryCode => $depth) {
    $addressFormats[$countryCode]['subdivision_depth'] = $depth;
}
// Address formats are stored in PHP, then manually transferred to
// AddressFormatRepository.
file_put_php('address_formats.php', $addressFormats);

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
    $data = var_export($data, true);
    // The var_export output is terrible, so try to get it as close as possible
    // to the final result.
    $array_keys = [
        '0 => ', '1 => ', '2 => ', '3 => ', '4 => ', '5 => ',
        '6 => ', '7 => ', '8 => ', '9 => ', '10 => ', '11 => ',
    ];
    $data = str_replace(['array (', "),\n", "=> \n  "], ['[', "],\n", '=> '], $data);
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
    $data = '<?php' . "\n\n" . '$data = ' . $data . ';';
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
function generate_subdivisions($countryCode, array $parents, $subdivisionPaths, $languages)
{
    $group = build_group($parents);
    $subdivisions = [];
    foreach ($subdivisionPaths as $subdivisionPath) {
        $definition = file_get_contents('raw/' . $subdivisionPath . '.json');
        $definition = json_decode($definition, true);
        // The lname is usable as a latin code when the key is non-latin.
        $code = $definition['key'];
        if (isset($definition['lname'])) {
            $code = $definition['lname'];
        }
        if (!isset($subdivisions[$group])) {
            $subdivisions[$group] = [
                'country_code' => $countryCode,
                'parents' => $parents,
                'locale' => '',
            ];
            if (isset($definition['lang']) && isset($definition['lname'])) {
                // Only add the locale if there's a local name.
                $subdivisions[$group]['locale'] = process_locale($definition['lang']);
            }
            if (count($subdivisions[$group]['parents']) < 2) {
              // A single parent is the same as the country code.
              unset($subdivisions[$group]['parents']);
            }
        }
        // (Ab)use the local_name field to hold latin translations. This allows
        // us to support only a single translation, but since our only example
        // here is Canada (with French), it will do.
        $translationLanguage = reset($languages);
        if ($translationLanguage) {
            $translation = file_get_contents('raw/' . $subdivisionPath . '--' . $translationLanguage . '.json');
            $translation = json_decode($translation, true);
            $subdivisions[$group]['locale'] = LocaleHelper::canonicalize($translationLanguage);
            $definition['lname'] = $definition['name'];
            $definition['name'] = $translation['name'];
        }
        // Remove the locale key if it wasn't filled.
        if (empty($subdivisions[$group]['locale'])) {
            unset($subdivisions[$group]['locale']);
        }
        // Generate the subdivision.
        $subdivisions[$group]['subdivisions'][$code] = create_subdivision_definition($countryCode, $definition);

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
    // All subdivisions have been removed. Remove the rest of the data.
    if (empty($subdivisions[$group]['subdivisions'])) {
        unset($subdivisions[$group]);
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
            'subdivision/' . $countryCode . '--*.json',
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
        } elseif ($rawDefinition['postprefix'] == 'SI-') {
            $rawDefinition['postprefix'] = 'SI- ';
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
function create_subdivision_definition($countryCode, $rawDefinition)
{
    $subdivision = [];
    if (isset($rawDefinition['lname'])) {
        // The lname was already chosen for the code in the parent function,
        // don't need to store it as the name cause SubdivisionRepository
        // optimizes for that.
        $subdivision['local_code'] = $rawDefinition['key'];
        if (isset($rawDefinition['name']) && $rawDefinition['key'] != $rawDefinition['name']) {
            $subdivision['local_name'] = $rawDefinition['name'];
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
 * Processes the locale string.
 */
function process_locale($locale) {
    // Be more precise when it comes to Chinese Simplified.
    if ($locale == 'zh') {
        $locale = 'zh-hans';
    }
    return LocaleHelper::canonicalize($locale);
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
        '%N3' => '%' . AddressField::FAMILY_NAME,
        '%N2' => '%' . AddressField::ADDITIONAL_NAME,
        '%N1' => '%' . AddressField::GIVEN_NAME,
        '%n' => "\n",
    ];
    $format = strtr($format, $replacements);
    // Make sure the newlines don't get eaten by var_export().
    $format = str_replace("\n", '\n', $format);

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
