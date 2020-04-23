<?php

/**
 * Generates the json files stored in resources/country.
 */

set_time_limit(0);
date_default_timezone_set('UTC');

include __DIR__ . '/../vendor/autoload.php';

$localeDirectory = __DIR__ . '/assets/cldr-localenames-full/main/';
$enCountries = $localeDirectory . 'en/territories.json';
$codeMappings = __DIR__ . '/assets/cldr-core/supplemental/codeMappings.json';
$currencyData = __DIR__ . '/assets/cldr-core/supplemental/currencyData.json';
if (!file_exists($enCountries)) {
    die("The $enCountries file was not found");
}
if (!file_exists($codeMappings)) {
    die("The $codeMappings file was not found");
}
if (!file_exists($currencyData)) {
    die("The $currencyData file was not found");
}
if (!function_exists('collator_create')) {
    // Reimplementing intl's collator would be a huge undertaking, so we
    // use it instead to presort the generated locale specific data.
    die('The intl extension was not found.');
}
if (!is_dir($localeDirectory)) {
    die("The $localeDirectory directory was not found");
}

$codeMappings = json_decode(file_get_contents($codeMappings), true);
$codeMappings = $codeMappings['supplemental']['codeMappings'];
$currencyData = json_decode(file_get_contents($currencyData), true);
$currencyData = $currencyData['supplemental']['currencyData'];
$englishData = json_decode(file_get_contents($enCountries), true);
$englishData = $englishData['main']['en']['localeDisplayNames']['territories'];

$baseData = generate_base_data($englishData, $codeMappings, $currencyData);
$localizations = generate_localizations($baseData, $englishData);
$localizations = filter_duplicate_localizations($localizations);

// Make sure we're starting from a clean slate.
if (is_dir(__DIR__ . '/country')) {
    die('The country/ directory must not exist.');
}

// Prepare the filesystem.
mkdir(__DIR__ . '/country');

// Write out the localizations.
foreach ($localizations as $locale => $localizedCountries) {
    $collator = collator_create($locale);
    uasort($localizedCountries, function ($a, $b) use ($collator) {
        return collator_compare($collator, $a, $b);
    });
    file_put_json(__DIR__ . '/country/' . $locale . '.json', $localizedCountries);
}

$availableLocales = array_keys($localizations);
sort($availableLocales);
// Base country definitions and available locales are stored
// in PHP, then manually transferred to CountryRepository.
$data = "<?php\n\n";
$data .= export_locales($availableLocales);
$data .= export_base_data($baseData);
file_put_contents(__DIR__ . '/country_data.php', $data);

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
 * Exports base data.
 */
function export_base_data($baseData)
{
    $export = '$baseData = [' . "\n";
    foreach ($baseData as $countryCode => $countryData) {
        $threeLetterCode = 'null';
        if (isset($countryData['three_letter_code'])) {
            $threeLetterCode = "'" . $countryData['three_letter_code'] . "'";
        }
        $numericCode = 'null';
        if (isset($countryData['numeric_code'])) {
            $numericCode = "'" . $countryData['numeric_code'] . "'";
        }
        $currencyCode = 'null';
        if (isset($countryData['currency_code'])) {
            $currencyCode = "'" . $countryData['currency_code'] . "'";
        }

        $export .= "    '" . $countryCode . "' => [";
        $export .= $threeLetterCode . ", " . $numericCode . ', ' . $currencyCode;
        $export .= "],\n";
    }
    $export .= "];";

    return $export;
}

/**
 * Exports locales.
 */
function export_locales($data)
{
    // Wrap the values in single quotes.
    $data = array_map(function ($value) {
        return "'" . $value . "'";
    }, $data);

    $export = '// ' . count($data) . " available locales. \n";
    $export .= '$locales = [' . "\n";
    $export .= '    ' . implode(', ', $data) . "\n";
    $export .= "];\n\n";

    return $export;
}

/**
 * Generates the base data.
 */
function generate_base_data(array $englishData, array $codeMappings, array $currencyData) {
    $ignoredCountries = [
        'AN', // Netherlands Antilles, no longer exists.
        'EU', 'QO', // European Union, Outlying Oceania. Not countries.
        'XA', 'XB',
        'ZZ', // Unknown region
    ];

    $baseData = [];
    foreach ($englishData as $countryCode => $countryName) {
        if (is_numeric($countryCode) || in_array($countryCode, $ignoredCountries)) {
            // Ignore continents, regions, uninhabited islands.
            continue;
        }
        if (strpos($countryCode, '-alt-') !== false) {
            // Ignore alternative names.
            continue;
        }

        // Countries are not guaranteed to have an alpha3 and/or numeric code.
        if (isset($codeMappings[$countryCode]['_alpha3'])) {
            $baseData[$countryCode]['three_letter_code'] = $codeMappings[$countryCode]['_alpha3'];
        }
        if (isset($codeMappings[$countryCode]['_numeric'])) {
            $baseData[$countryCode]['numeric_code'] = $codeMappings[$countryCode]['_numeric'];
        }

        // Determine the current currency for this country.
        if (isset($currencyData['region'][$countryCode])) {
            $currencies = prepare_currencies($currencyData['region'][$countryCode]);
            if ($currencies) {
                $currencyCodes = array_keys($currencies);
                $currentCurrency = end($currencyCodes);
                $baseData[$countryCode]['currency_code'] = $currentCurrency;
            }
        }
    }

    ksort($baseData);

    return $baseData;
}

/**
 * Generates the localizations.
 */
function generate_localizations(array $baseData, array $englishData) {
    global $localeDirectory;

    $localizations = [];
    $untranslatedCounts = [];
    foreach (discover_locales() as $locale) {
        $data = json_decode(file_get_contents($localeDirectory . $locale . '/territories.json'), true);
        $data = $data['main'][$locale]['localeDisplayNames']['territories'];
        foreach ($data as $countryCode => $countryName) {
            if (isset($baseData[$countryCode])) {
                // This country name is untranslated, use the english version.
                if ($countryCode == str_replace('_', '-', $countryName)) {
                    $countryName = $englishData[$countryCode];
                    // Maintain a count of untranslated countries per locale.
                    $untranslatedCounts += [$locale => 0];
                    $untranslatedCounts[$locale]++;
                }

                $localizations[$locale][$countryCode] = $countryName;
            }
        }
    }

    // Ignore locales that are more than 80% untranslated.
    foreach ($untranslatedCounts as $locale => $count) {
        $totalCount = count($localizations[$locale]);
        $untranslatedPercentage = $count * (100 / $totalCount);
        if ($untranslatedPercentage >= 80) {
            unset($localizations[$locale]);
        }
    }

    return $localizations;
}

/**
 * Filters out duplicate localizations (same as their parent locale).
 *
 * For example, "fr-FR" will be removed if "fr" has the same data.
 */
function filter_duplicate_localizations(array $localizations) {
    $duplicates = [];
    foreach ($localizations as $locale => $localizedCountries) {
        if ($parentLocale = \CommerceGuys\Addressing\Locale::getParent($locale)) {
            $parentCountries = isset($localizations[$parentLocale]) ? $localizations[$parentLocale] : [];
            $diff = array_udiff($localizedCountries, $parentCountries, function ($first, $second) {
                return ($first == $second) ? 0 : 1;
            });

            if (empty($diff)) {
                // The duplicates are not removed right away because they might
                // still be needed for other duplicate checks (for example,
                // when there are locales like bs-Latn-BA, bs-Latn, bs).
                $duplicates[] = $locale;
            }
        }
    }
    foreach ($duplicates as $locale) {
        unset($localizations[$locale]);
    }

    return $localizations;
}

/**
 * Creates a list of available locales.
 */
function discover_locales() {
    global $localeDirectory;

    // Locales listed without a "-" match all variants.
    // Locales listed with a "-" match only those exact ones.
    $ignoredLocales = [
        // Esperanto, Interlingua, Volapuk are made up languages.
        'eo', 'ia', 'vo',
        // Church Slavic, Manx, Prussian are historical languages.
        'cu', 'gv', 'prg',
        // Valencian differs from its parent only by a single character (è/é).
        'ca-ES-VALENCIA',
        // Africa secondary languages.
        'agq', 'ak', 'am', 'asa', 'bas', 'bem', 'bez', 'bm', 'cgg', 'dav',
        'dje', 'dua', 'dyo', 'ebu', 'ee', 'ewo', 'ff', 'ff-Latn', 'guz',
        'ha', 'ig', 'jgo', 'jmc', 'kab', 'kam', 'kea', 'kde', 'ki', 'kkj',
        'kln', 'khq', 'ksb', 'ksf', 'lag', 'luo', 'luy', 'lu', 'lg', 'ln',
        'mas', 'mer', 'mua', 'mgo', 'mgh', 'mfe', 'naq', 'nd', 'nmg', 'nnh',
        'nus', 'nyn', 'om', 'pcm', 'rof', 'rwk', 'saq', 'seh', 'ses', 'sbp',
        'sg', 'shi', 'sn', 'teo', 'ti', 'tzm', 'twq', 'vai', 'vai-Latn', 'vun',
        'wo', 'xog', 'xh', 'zgh', 'yav', 'yo', 'zu',
        // Europe secondary languages.
        'br', 'dsb', 'fo', 'fur', 'fy', 'hsb', 'ksh', 'kw', 'nds', 'or', 'rm',
        'se', 'smn', 'wae',
        // Other infrequently used locales.
        'ceb', 'ccp', 'chr', 'ckb', 'haw', 'ii', 'jv', 'kl', 'kn', 'lkt',
        'lrc', 'mi', 'mzn', 'os', 'qu', 'row', 'sah', 'su', 'tt', 'ug', 'yi',
        // Special "grouping" locales.
        'root', 'en-US-POSIX',
    ];

    // Gather available locales.
    $locales = [];
    if ($handle = opendir($localeDirectory)) {
        while (false !== ($entry = readdir($handle))) {
            if (substr($entry, 0, 1) != '.') {
                $entryParts = explode('-', $entry);
                if (!in_array($entry, $ignoredLocales) && !in_array($entryParts[0], $ignoredLocales)) {
                    $locales[] = $entry;
                }
            }
        }
        closedir($handle);
    }

    return $locales;
}

/**
 * Prepares the currencies for a specific country.
 */
function prepare_currencies($currencies)
{
    if (empty($currencies)) {
        return [];
    }
    // Rekey the array by currency code.
    foreach ($currencies as $index => $realCurrencies) {
        foreach ($realCurrencies as $currencyCode => $currency) {
            $currencies[$currencyCode] = $currency;
        }
        unset($currencies[$index]);
    }
    // Remove non-tender currencies.
    $currencies = array_filter($currencies, function ($currency) {
        return !isset($currency['_tender']) || $currency['_tender'] != 'false';
    });
    // Sort by _from date.
    uasort($currencies, 'compare_from_dates');

    return $currencies;
}

/**
 * uasort callback for comparing arrays using their "_from" dates.
 */
function compare_from_dates($a, $b)
{
    $a = new DateTime($a['_from']);
    $b = new DateTime($b['_from']);
    // DateTime overloads the comparison providers.
    if ($a == $b) {
        return 0;
    }
    return ($a < $b) ? -1 : 1;
}
