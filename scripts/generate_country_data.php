<?php

/**
 * Generates the json files stored in resources/country.
 */

set_time_limit(0);
date_default_timezone_set('UTC');

include __DIR__ . '/../vendor/autoload.php';

$dataDirectory = __DIR__ . '/assets/cldr/cldr-json';
if (!is_dir($dataDirectory)) {
    die("The $dataDirectory directory was not found");
}
if (!function_exists('collator_create')) {
    // Reimplementing intl's collator would be a huge undertaking, so we
    // use it instead to presort the generated locale specific data.
    die('The intl extension was not found.');
}
// Make sure we're starting from a clean slate.
if (is_dir(__DIR__ . '/country')) {
    die('The country/ directory must not exist.');
}

$englishData = json_decode(file_get_contents($dataDirectory . '/cldr-localenames-full/main/en/territories.json'), true);
$englishData = $englishData['main']['en']['localeDisplayNames']['territories'];

$baseData = generate_base_data($englishData, $dataDirectory);
$localizations = generate_localizations($baseData, $englishData, $dataDirectory);
$localizations = filter_duplicate_localizations($localizations);

// Prepare the filesystem.
if (!mkdir($concurrentDirectory = __DIR__ . '/country') && !is_dir($concurrentDirectory)) {
    throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
}

// Write out the localizations.
foreach ($localizations as $locale => $localizedCountries) {
    $collator = collator_create($locale);
    uasort($localizedCountries, static function ($a, $b) use ($collator) {
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
function file_put_json(string $filename, array $data): void
{
    $data = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    // Indenting with tabs instead of 4 spaces gives us 20% smaller files.
    $data = str_replace('    ', "\t", $data);
    file_put_contents($filename, $data);
}

/**
 * Exports base data.
 */
function export_base_data(array $baseData): string
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
function export_locales(array $data): string
{
    // Wrap the values in single quotes.
    $data = array_map(static function ($value) {
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
function generate_base_data(array $englishData, string $dataDirectory): array
{
    $ignoredCountries = [
        'AN', // Netherlands Antilles, no longer exists.
        'EU', 'QO', // European Union, Outlying Oceania. Not countries.
        'XA', 'XB',
        'ZZ', // Unknown region
    ];

    $codeMappings = json_decode(file_get_contents($dataDirectory . '/cldr-core/supplemental/codeMappings.json'), true);
    $codeMappings = $codeMappings['supplemental']['codeMappings'];
    $currencyData = json_decode(file_get_contents($dataDirectory . '/cldr-core/supplemental/currencyData.json'), true);
    $currencyData = $currencyData['supplemental']['currencyData'];

    $baseData = [];
    foreach ($englishData as $countryCode => $countryName) {
        if (is_numeric($countryCode) || in_array($countryCode, $ignoredCountries)) {
            // Ignore continents, regions, uninhabited islands.
            continue;
        }
        if (str_contains($countryCode, '-alt-')) {
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
function generate_localizations(array $baseData, array $englishData, string $dataDirectory): array
{
    $localizations = [];
    foreach (collect_locales($dataDirectory) as $locale) {
        $data = json_decode(file_get_contents($dataDirectory . '/cldr-localenames-full/main/' . $locale . '/territories.json'), true);
        $data = $data['main'][$locale]['localeDisplayNames']['territories'];
        foreach ($data as $countryCode => $countryName) {
            if (isset($baseData[$countryCode])) {
                // This country name is untranslated, use the english version.
                if ($countryCode == str_replace('_', '-', $countryName)) {
                    $countryName = $englishData[$countryCode];
                }

                $localizations[$locale][$countryCode] = $countryName;
            }
        }
    }

    return $localizations;
}

/**
 * Filters out duplicate localizations (same as their parent locale).
 *
 * For example, "fr-FR" will be removed if "fr" has the same data.
 */
function filter_duplicate_localizations(array $localizations): array
{
    $duplicates = [];
    foreach ($localizations as $locale => $localizedCountries) {
        if ($parentLocale = \CommerceGuys\Addressing\Locale::getParent($locale)) {
            $parentCountries = $localizations[$parentLocale] ?? [];
            $diff = array_udiff($localizedCountries, $parentCountries, static function ($first, $second) {
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
function collect_locales(string $dataDirectory): array
{
    // Locales listed without a "-" match all variants.
    // Locales listed with a "-" match only those exact ones.
    $ignoredLocales = [
        // English is our fallback, we don't need another.
        "und",
        // Esperanto, Interlingua, Volapuk are made up languages.
        "eo", "ia", "vo",
        // Belarus (Classical orthography), Church Slavic, Manx, Prussian are historical.
        "be-tarask", "cu", "gv", "prg",
        // Valencian differs from its parent only by a single character (è/é).
        "ca-ES-valencia",
        // Infrequently used locales.
        "jv", "kn", "sd", "yo",
    ];

    // Start from the list of locales with a "modern" coverage level.
    $coverageLevels = json_decode(file_get_contents($dataDirectory . '/cldr-core/coverageLevels.json'), true);
    $coverageLevels = array_filter($coverageLevels['effectiveCoverageLevels'], static function ($level) {
        return $level == 'modern';
    });
    $locales = array_keys($coverageLevels);

    // Remove ignored locales.
    $locales = array_filter($locales, static function ($locale) use ($ignoredLocales) {
        $localeParts = explode('-', $locale);

        return !in_array($locale, $ignoredLocales) && !in_array($localeParts[0], $ignoredLocales);
    });

    return $locales;
}

/**
 * Prepares the currencies for a specific country.
 */
function prepare_currencies(array $currencies): array
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
    $currencies = array_filter($currencies, static function ($currency) {
        return !isset($currency['_tender']) || $currency['_tender'] != 'false';
    });
    // Remove ex-currencies.
    $currencies = array_filter($currencies, static function ($currency) {
        return !isset($currency['_to']);
    });
    // Sort by _from date.
    uasort($currencies, 'compare_from_dates');

    return $currencies;
}

/**
 * uasort callback for comparing arrays using their "_from" dates.
 * @throws Exception
 */
function compare_from_dates($a, $b): int
{
    $a = new DateTime($a['_from']);
    $b = new DateTime($b['_from']);
    // DateTime overloads the comparison providers.
    if ($a == $b) {
        return 0;
    }
    return ($a < $b) ? -1 : 1;
}
