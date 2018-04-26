<?php

namespace CommerceGuys\Addressing;

/**
 * Provides helpers for matching postal codes.
 */
class PostalCodeHelper
{
    /**
     * Checks whether the provided postal code matches the provided rules.
     *
     * @param string $postalCode  The postal code.
     * @param string $includeRule The rule for included postal codes.
     * @param string $excludeRule (Optional) The rule for excluded postal codes.
     *
     * @return bool True if the provided postal code matches the provided
     *              rules, false otherwise.
     */
    public static function match($postalCode, $includeRule, $excludeRule = '')
    {
        $matchIncluded = true;
        if ($includeRule) {
            $matchIncluded = self::matchRule($postalCode, $includeRule);
        }
        $matchExcluded = false;
        if ($excludeRule) {
            $matchExcluded = self::matchRule($postalCode, $excludeRule);
        }

        return $matchIncluded && !$matchExcluded;
    }

    /**
     * Checks whether the provided postal code matches the provided rule.
     *
     * @param string $postalCode The postal code.
     * @param string $rule       The rule. Can be a regular expression
     *                           ("/(35|38)[0-9]{3}/") or comma-separated list,
     *                            including ranges ("98, 100:200, 250").
     *
     * @return bool True if the provided postal code matches the provided
     *              rules, false otherwise.
     */
    protected static function matchRule($postalCode, $rule)
    {
        if (substr($rule, 0, 1) == '/' && substr($rule, -1, 1) == '/') {
            $match = preg_match($rule, $postalCode);
        } else {
            $match = in_array($postalCode, self::buildList($rule));
        }

        return $match;
    }

    /**
     * Builds a list of postal codes from the provided string.
     *
     * Expands any ranges into full values (e.g. "1:3" becomes "1, 2, 3").
     *
     * @param string $postalCodes The postal codes.
     *
     * @return array The list of postal codes.
     */
    protected static function buildList($postalCodes)
    {
        $postalCodeList = [];
        foreach (explode(',', $postalCodes) as $postalCode) {
            $postalCode = trim($postalCode);
            if (strpos($postalCode, ':') !== false) {
                $postalCodeRange = explode(':', $postalCode);
                if (is_numeric($postalCodeRange[0]) && is_numeric($postalCodeRange[1])) {
                    $postalCodeRange = range($postalCodeRange[0], $postalCodeRange[1]);
                    $postalCodeList = array_merge($postalCodeList, $postalCodeRange);
                }
            } else {
                $postalCodeList[] = $postalCode;
            }
        }

        return $postalCodeList;
    }
}
