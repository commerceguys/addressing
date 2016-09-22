<?php

namespace CommerceGuys\Addressing;

/**
 * The 1.0 branch of Addressing introduced one big change:
 * - Subdivision ids were removed, the subdivision code is now stored directly.
 * This change requires updating every stored address.
 * This class provides helpers for performing that update.
 */
class UpdateHelper
{
    /**
     * A static cache of the subdivision $oldId => $newId map.
     *
     * @var array
     */
    static protected $subdivisionUpdateMap = [];

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
    public static function updateSubdivision($oldValue)
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
    public static function loadSubdivisionUpdateMap()
    {
        if (empty(static::$subdivisionUpdateMap)) {
            $path = __DIR__ . '/../resources/';
            $rawMap = file_get_contents($path . 'subdivision_update_map.json');
            static::$subdivisionUpdateMap = json_decode($rawMap, true);
        }

        return static::$subdivisionUpdateMap;
    }
}
