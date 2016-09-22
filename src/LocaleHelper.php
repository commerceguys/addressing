<?php

namespace CommerceGuys\Addressing;

/**
 * Provides helpers for handling locales.
 */
final class LocaleHelper
{
    /**
     * Checks whether the two locales match.
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

        $firstLocale = str_replace('-', '_', strtolower($firstLocale));
        $firstLocaleParts = explode('_', $firstLocale);
        $secondLocale = str_replace('-', '_', strtolower($secondLocale));
        $secondLocaleParts = explode('_', $secondLocale);
        // Language codes must match.
        if ($firstLocaleParts[0] != $secondLocaleParts[0]) {
            return false;
        }
        // @todo Match scripts, if found.

        return true;
    }

    /**
     * Canonicalize the given locale.
     *
     * @param string $locale The locale.
     *
     * @return string The canonicalized locale.
     */
    public static function canonicalize($locale = null)
    {
        if (is_null($locale)) {
            return $locale;
        }

        $locale = str_replace('-', '_', strtolower($locale));
        $localeParts = explode('_', $locale);
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
     * Gets all variants of a locale.
     *
     * For example, "bs-Cyrl-BA" has the following variants:
     * 1) bs-Cyrl-BA
     * 2) bs-Cyrl
     * 3) bs
     *
     * @param string $locale The locale (i.e. fr-FR).
     *
     * @return array An array of all variants of a locale.
     */
    public static function getVariants($locale)
    {
        $localeVariants = [];
        $localeParts = explode('-', $locale);
        while (!empty($localeParts)) {
            $localeVariants[] = implode('-', $localeParts);
            array_pop($localeParts);
        }

        return $localeVariants;
    }
}
