<?php

namespace CommerceGuys\Addressing;

/**
 * The 1.0 branch of Addressing introduced two big changes:
 * - The recipient was split into given_name, additional_name, family_name.
 * - Subdivision IDs were removed, the subdivision code is now stored directly.
 * These two changes require updating every stored address.
 * This class provides helpers for performing that update.
 */
class UpdateHelper
{
    /**
     * A static cache of the subdivision $oldId => $newId map.
     *
     * @var array
     */
    protected static $subdivisionUpdateMap = [];

    /**
     * Splits the recipient into givenName and familyName fields.
     *
     * @param string $recipient   The recipient.
     * @param string $countryCode The country code.
     *
     * @return array The result, with givenName and familyName keys.
     */
    public static function splitRecipient($recipient, $countryCode): array
    {
        // Countries that write the family name before the given name.
        $reverseCountries = [
            'KH', 'CN', 'HU', 'JP', 'KO', 'MG', 'TW', 'VN',
        ];
        $recipientParts = explode(' ', $recipient);
        if (in_array($countryCode, $reverseCountries)) {
            $familyName = array_shift($recipientParts);
            $givenName = implode(' ', $recipientParts);
        } else {
            $familyName = array_pop($recipientParts);
            $givenName = implode(' ', $recipientParts);
        }

        return ['givenName' => $givenName, 'familyName' => $familyName];
    }

    /**
     * Updates the subdivision.
     *
     * Used for updating the administrative area, locality, dependent locality
     * address properties.
     *
     * @param string $oldValue The old value.
     *
     * @return string The new value.
     */
    public static function updateSubdivision($oldValue): string
    {
        // Countries that have defined subdivisions.
        $supportedCountries = [
            'AD', 'AE', 'AM', 'AR', 'AU', 'BR', 'BS', 'CA', 'CL', 'CN', 'CV',
            'EG', 'ES', 'HK', 'ID', 'IE', 'IN', 'IT', 'JM', 'JP', 'KN', 'KR',
            'KY', 'MX', 'MY', 'NG', 'NI', 'NR', 'PH', 'RU', 'SO', 'SR', 'SV',
            'TH', 'TR', 'TV', 'TW', 'UA', 'US', 'UY', 'VE', 'VN'
        ];
        // Countries where the subdivision IDs just need the prefix removed.
        $simpleAdministrativeAreas = [
            'AU', 'BR', 'CA', 'IT', 'US',
        ];

        if (substr($oldValue, 2, 1) != '-') {
            // This is a full value, not the ID of a predefined value.
            return $oldValue;
        }
        $countryCode = substr($oldValue, 0, 2);
        if (!in_array($countryCode, $supportedCountries)) {
            // Unrecognized country code.
            return $oldValue;
        }

        // Prefixed administrative area.
        $parts = explode('-', $oldValue);
        $isAdministrativeArea = count($parts) == 2;
        if ($isAdministrativeArea && in_array($countryCode, $simpleAdministrativeAreas)) {
            return $parts[1];
        }
        // Mapped value.
        $updateMap = static::loadSubdivisionUpdateMap();
        if (isset($updateMap[$oldValue])) {
            return $updateMap[$oldValue];
        }

        return $oldValue;
    }

    /**
     * Loads the subdivision update map.
     *
     * @return array The update map.
     */
    public static function loadSubdivisionUpdateMap(): array
    {
        if (empty(static::$subdivisionUpdateMap)) {
            $path = __DIR__ . '/../resources/';
            $rawMap = file_get_contents($path . 'subdivision_update_map.json');
            static::$subdivisionUpdateMap = json_decode($rawMap, true);
        }

        return static::$subdivisionUpdateMap;
    }
}
