<?php

namespace CommerceGuys\Addressing;

use CommerceGuys\Addressing\Exception\UnknownLocaleException;

/**
 * Provides locale handling logic.
 *
 * Copied from commerceguys/intl to avoid a dependency.
 */
final class Locale
{
    /**
     * Locale aliases.
     *
     * @var array
     */
    protected static $aliases = [
        'az-AZ' => 'az-Latn-AZ',
        'bs-BA' => 'bs-Latn-BA',
        'ha-GH' => 'ha-Latn-GH',
        'ha-NE' => 'ha-Latn-NE',
        'ha-NG' => 'ha-Latn-NG',
        'in' => 'id',
        'in-ID' => 'id-ID',
        'iw' => 'he',
        'iw-IL' => 'he-IL',
        'kk-KZ' => 'kk-Cyrl-KZ',
        'ks-IN' => 'ks-Arab-IN',
        'ky-KG' => 'ky-Cyrl-KG',
        'mn-MN' => 'mn-Cyrl-MN',
        'mo' => 'ro-MD',
        'ms-BN' => 'ms-Latn-BN',
        'ms-MY' => 'ms-Latn-MY',
        'ms-SG' => 'ms-Latn-SG',
        'no' => 'nb',
        'no-NO' => 'nb-NO',
        'no-NO-NY' => 'nn-NO',
        'pa-IN' => 'pa-Guru-IN',
        'pa-PK' => 'pa-Arab-PK',
        'sh' => 'sr-Latn',
        'sh-BA' => 'sr-Latn-BA',
        'sh-CS' => 'sr-Latn-RS',
        'sh-YU' => 'sr-Latn-RS',
        'shi-MA' => 'shi-Tfng-MA',
        'sr-BA' => 'sr-Cyrl-BA',
        'sr-ME' => 'sr-Latn-ME',
        'sr-RS' => 'sr-Cyrl-RS',
        'sr-XK' => 'sr-Cyrl-XK',
        'tl' => 'fil',
        'tl-PH' => 'fil-PH',
        'tzm-MA' => 'tzm-Latn-MA',
        'ug-CN' => 'ug-Arab-CN',
        'uz-AF' => 'uz-Arab-AF',
        'uz-UZ' => 'uz-Latn-UZ',
        'vai-LR' => 'vai-Vaii-LR',
        'zh-CN' => 'zh-Hans-CN',
        'zh-HK' => 'zh-Hant-HK',
        'zh-MO' => 'zh-Hant-MO',
        'zh-SG' => 'zh-Hans-SG',
        'zh-TW' => 'zh-Hant-TW',
    ];

    /**
     * Locale parents.
     *
     * @var array
     */
    protected static $parents = [
        'en-150' => 'en-001',
        'en-AG' => 'en-001',
        'en-AI' => 'en-001',
        'en-AU' => 'en-001',
        'en-BB' => 'en-001',
        'en-BM' => 'en-001',
        'en-BS' => 'en-001',
        'en-BW' => 'en-001',
        'en-BZ' => 'en-001',
        'en-CA' => 'en-001',
        'en-CC' => 'en-001',
        'en-CK' => 'en-001',
        'en-CM' => 'en-001',
        'en-CX' => 'en-001',
        'en-CY' => 'en-001',
        'en-DG' => 'en-001',
        'en-DM' => 'en-001',
        'en-ER' => 'en-001',
        'en-FJ' => 'en-001',
        'en-FK' => 'en-001',
        'en-FM' => 'en-001',
        'en-GB' => 'en-001',
        'en-GD' => 'en-001',
        'en-GG' => 'en-001',
        'en-GH' => 'en-001',
        'en-GI' => 'en-001',
        'en-GM' => 'en-001',
        'en-GY' => 'en-001',
        'en-HK' => 'en-001',
        'en-IE' => 'en-001',
        'en-IL' => 'en-001',
        'en-IM' => 'en-001',
        'en-IN' => 'en-001',
        'en-IO' => 'en-001',
        'en-JE' => 'en-001',
        'en-JM' => 'en-001',
        'en-KE' => 'en-001',
        'en-KI' => 'en-001',
        'en-KN' => 'en-001',
        'en-KY' => 'en-001',
        'en-LC' => 'en-001',
        'en-LR' => 'en-001',
        'en-LS' => 'en-001',
        'en-MG' => 'en-001',
        'en-MO' => 'en-001',
        'en-MS' => 'en-001',
        'en-MT' => 'en-001',
        'en-MU' => 'en-001',
        'en-MW' => 'en-001',
        'en-MY' => 'en-001',
        'en-NA' => 'en-001',
        'en-NF' => 'en-001',
        'en-NG' => 'en-001',
        'en-NR' => 'en-001',
        'en-NU' => 'en-001',
        'en-NZ' => 'en-001',
        'en-PG' => 'en-001',
        'en-PH' => 'en-001',
        'en-PK' => 'en-001',
        'en-PN' => 'en-001',
        'en-PW' => 'en-001',
        'en-RW' => 'en-001',
        'en-SB' => 'en-001',
        'en-SC' => 'en-001',
        'en-SD' => 'en-001',
        'en-SG' => 'en-001',
        'en-SH' => 'en-001',
        'en-SL' => 'en-001',
        'en-SS' => 'en-001',
        'en-SX' => 'en-001',
        'en-SZ' => 'en-001',
        'en-TC' => 'en-001',
        'en-TK' => 'en-001',
        'en-TO' => 'en-001',
        'en-TT' => 'en-001',
        'en-TV' => 'en-001',
        'en-TZ' => 'en-001',
        'en-UG' => 'en-001',
        'en-VC' => 'en-001',
        'en-VG' => 'en-001',
        'en-VU' => 'en-001',
        'en-WS' => 'en-001',
        'en-ZA' => 'en-001',
        'en-ZM' => 'en-001',
        'en-ZW' => 'en-001',
        'en-AT' => 'en-150',
        'en-BE' => 'en-150',
        'en-CH' => 'en-150',
        'en-DE' => 'en-150',
        'en-DK' => 'en-150',
        'en-FI' => 'en-150',
        'en-NL' => 'en-150',
        'en-SE' => 'en-150',
        'en-SI' => 'en-150',
        'es-AR' => 'es-419',
        'es-BO' => 'es-419',
        'es-BR' => 'es-419',
        'es-BZ' => 'es-419',
        'es-CL' => 'es-419',
        'es-CO' => 'es-419',
        'es-CR' => 'es-419',
        'es-CU' => 'es-419',
        'es-DO' => 'es-419',
        'es-EC' => 'es-419',
        'es-GT' => 'es-419',
        'es-HN' => 'es-419',
        'es-MX' => 'es-419',
        'es-NI' => 'es-419',
        'es-PA' => 'es-419',
        'es-PE' => 'es-419',
        'es-PR' => 'es-419',
        'es-PY' => 'es-419',
        'es-SV' => 'es-419',
        'es-US' => 'es-419',
        'es-UY' => 'es-419',
        'es-VE' => 'es-419',
        'nb' => 'no',
        'nn' => 'no',
        'pt-AO' => 'pt-PT',
        'pt-CH' => 'pt-PT',
        'pt-CV' => 'pt-PT',
        'pt-FR' => 'pt-PT',
        'pt-GQ' => 'pt-PT',
        'pt-GW' => 'pt-PT',
        'pt-LU' => 'pt-PT',
        'pt-MO' => 'pt-PT',
        'pt-MZ' => 'pt-PT',
        'pt-ST' => 'pt-PT',
        'pt-TL' => 'pt-PT',
        'az-Arab' => 'root',
        'az-Cyrl' => 'root',
        'blt-Latn' => 'root',
        'bs-Cyrl' => 'root',
        'byn-Latn' => 'root',
        'en-Dsrt' => 'root',
        'en-Shaw' => 'root',
        'hi-Latn' => 'root',
        'iu-Latn' => 'root',
        'kk-Arab' => 'root',
        'ks-Deva' => 'root',
        'ku-Arab' => 'root',
        'ky-Arab' => 'root',
        'ky-Latn' => 'root',
        'ml-Arab' => 'root',
        'mn-Mong' => 'root',
        'mni-Mtei' => 'root',
        'ms-Arab' => 'root',
        'pa-Arab' => 'root',
        'sat-Deva' => 'root',
        'sd-Deva' => 'root',
        'sd-Khoj' => 'root',
        'sd-Sind' => 'root',
        'so-Arab' => 'root',
        'sr-Latn' => 'root',
        'sw-Arab' => 'root',
        'tg-Arab' => 'root',
        'uz-Arab' => 'root',
        'uz-Cyrl' => 'root',
        'yue-Hans' => 'root',
        'zh-Hant' => 'root',
        'zh-Hant-MO' => 'zh-Hant-HK',
    ];

    /**
     * Checks whether two locales match.
     *
     * @param string $firstLocale  The first locale.
     * @param string $secondLocale The second locale.
     *
     * @return bool TRUE if the locales match, FALSE otherwise.
     */
    public static function match($firstLocale, $secondLocale)
    {
        if (empty($firstLocale) || empty($secondLocale)) {
            return false;
        }

        return self::canonicalize($firstLocale) === self::canonicalize($secondLocale);
    }

    /**
     * Checks whether two locales have at least one common candidate.
     *
     * For example, "de" and "de-AT" will match because they both have
     * "de" in common. This is useful for partial locale matching.
     *
     * @see self::getCandidates
     *
     * @param string $firstLocale  The first locale.
     * @param string $secondLocale The second locale.
     *
     * @return bool TRUE if there is a common candidate, FALSE otherwise.
     */
    public static function matchCandidates($firstLocale, $secondLocale)
    {
        if (empty($firstLocale) || empty($secondLocale)) {
            return false;
        }

        $firstLocale = self::canonicalize($firstLocale);
        $secondLocale = self::canonicalize($secondLocale);
        $firstLocaleCandidates = self::getCandidates($firstLocale);
        $secondLocaleCandidates = self::getCandidates($secondLocale);

        return (bool) array_intersect($firstLocaleCandidates, $secondLocaleCandidates);
    }

    /**
     * Resolves the locale from the available locales.
     *
     * Takes all locale candidates for the requested locale
     * and fallback locale, searches for them in the available
     * locale list. The first found locale is returned.
     * If no candidate is found in the list, an exception is thrown.
     *
     * @see self::getCandidates
     *
     * @param array  $availableLocales The available locales.
     * @param string $locale           The requested locale (i.e. fr-FR).
     * @param string $fallbackLocale   A fallback locale (i.e "en").
     *
     * @return string
     *
     * @throws UnknownLocaleException
     */
    public static function resolve(array $availableLocales, $locale, $fallbackLocale = null)
    {
        $locale = self::canonicalize($locale);
        $resolvedLocale = null;
        foreach (self::getCandidates($locale, $fallbackLocale) as $candidate) {
            if (in_array($candidate, $availableLocales)) {
                $resolvedLocale = $candidate;
                break;
            }
        }
        // No locale could be resolved, stop here.
        if (!$resolvedLocale) {
            throw new UnknownLocaleException($locale);
        }

        return $resolvedLocale;
    }

    /**
     * Canonicalizes the given locale.
     *
     * Standardizes separators and capitalization, turning
     * a locale such as "sr_rs_latn" into "sr-RS-Latn".
     *
     * @param string $locale The locale.
     *
     * @return string The canonicalized locale.
     */
    public static function canonicalize($locale)
    {
        if (empty($locale)) {
            return $locale;
        }

        $locale = str_replace('_', '-', strtolower($locale));
        $localeParts = explode('-', $locale);
        foreach ($localeParts as $index => $part) {
            if ($index === 0) {
                // The language code should stay lowercase.
                continue;
            }

            if (strlen($part) == 4) {
                // Script code.
                $localeParts[$index] = ucfirst($part);
            } else {
                // Country or variant code.
                $localeParts[$index] = strtoupper($part);
            }
        }

        return implode('-', $localeParts);
    }

    /**
     * Gets locale candidates.
     *
     * For example, "bs-Cyrl-BA" has the following candidates:
     * 1) bs-Cyrl-BA
     * 2) bs-Cyrl
     * 3) bs
     *
     * The locale is de-aliased, e.g. the candidates for "sh" are:
     * 1) sr-Latn
     * 2) sr
     *
     * @param string $locale         The locale (i.e. fr-FR).
     * @param string $fallbackLocale A fallback locale (i.e "en").
     *
     * @return array An array of all variants of a locale.
     */
    public static function getCandidates($locale, $fallbackLocale = null)
    {
        $locale = self::replaceAlias($locale);
        $candidates = [$locale];
        while ($parent = self::getParent($locale)) {
            $candidates[] = $parent;
            $locale = $parent;
        }
        if (isset($fallbackLocale)) {
            $candidates[] = $fallbackLocale;
            while ($parent = self::getParent($fallbackLocale)) {
                $candidates[] = $parent;
                $fallbackLocale = $parent;
            }
        }

        return array_unique($candidates);
    }

    /**
     * Gets the parent for the given locale.
     *
     * @param string $locale
     *   The locale.
     *
     * @return string|null
     *   The parent, or null if none found.
     */
    public static function getParent($locale)
    {
        $parent = null;
        if (isset(self::$parents[$locale])) {
            $parent = self::$parents[$locale];
        } elseif (strpos($locale, '-') !== false) {
            $localeParts = explode('-', $locale);
            array_pop($localeParts);
            $parent = implode('-', $localeParts);
        }
        // The library doesn't have data for the empty 'root' locale, it
        // is more user friendly to use the configured fallback instead.
        if ($parent == 'root') {
            $parent = null;
        }

        return $parent;
    }

    /**
     * Replaces a locale alias with the real locale.
     *
     * For example, "zh-CN" is replaced with "zh-Hans-CN".
     *
     * @param string $locale The locale.
     *
     * @return string The locale.
     */
    public static function replaceAlias($locale)
    {
        if (!empty($locale) && isset(self::$aliases[$locale])) {
            $locale = self::$aliases[$locale];
        }

        return $locale;
    }
}
