<?php

/**
 * Generates the address_format_changes.json file.
 */

set_time_limit(0);
date_default_timezone_set('UTC');

// Create a list of changes between the new and the old definitions.
$addressFormats = load_definitions('address_format');
$previousAddressFormats = load_definitions('../resources/address_format');
$addressFormatChanges = load_change_listing('address_format');
$addressFormatChanges[] = generate_address_format_changes($previousAddressFormats, $addressFormats);
file_put_json('address_format_changes.json', $addressFormatChanges);

echo "Generated a list of changes.\n";

/**
 * Converts the provided data into json and writes it to the disk.
 */
function file_put_json($filename, $data)
{
    $data = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    file_put_contents($filename, $data);
}

/**
 * Loads all definitions of the provided type (address_format or subdivision).
 */
function load_definitions($path)
{
    $data = [];
    if ($handle = opendir($path)) {
        while (false !== ($entry = readdir($handle))) {
            if (substr($entry, 0, 1) != '.') {
                $id = strtok($entry, '.');
                $data[$id] = json_decode(file_get_contents($path . '/' . $entry), true);
            }
        }
        closedir($handle);
    }

    return $data;
}

/**
 * Loads the changes file for the provided type (address_format or subdivision).
 */
function load_change_listing($type)
{
    $changes = @file_get_contents('../resources/' . $type . '_changes.json');
    if (!empty($changes)) {
        $changes = json_decode($changes, true);
    } else {
        $changes = [];
    }

    return $changes;
}

/**
 * Generates the changes between two address format collections.
 */
function generate_address_format_changes($oldAddressFormats, $newAddressFormats)
{
    $changes = [
        'date' => date('c'),
        'added' => array_keys(array_diff_key($newAddressFormats, $oldAddressFormats)),
        'removed' => array_keys(array_diff_key($oldAddressFormats, $newAddressFormats)),
        'modified' => array_keys(array_udiff_assoc(
            // Compare only the values of common keys.
            array_intersect_key($newAddressFormats, $oldAddressFormats),
            array_intersect_key($oldAddressFormats, $newAddressFormats),
            'compare_arrays'
        )),
    ];

    return $changes;
}

/**
 * Callback for array_udiff_assoc.
 */
function compare_arrays($a, $b)
{
    // Sort the keys so that they don't influence the comparison.
    ksort($a);
    ksort($b);

    return ($a === $b) ? 0 : -1;
}
