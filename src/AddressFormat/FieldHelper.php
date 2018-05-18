<?php

namespace CommerceGuys\Addressing\AddressFormat;

/**
 * Provides additional logic for handling fields.
 */
final class FieldHelper
{
    /**
     * Gets the list of used fields, grouped by line.
     *
     * Used for generating address forms.
     *
     * @param string $formatString The format string.
     *
     * @return array An array of address fields grouped by line, in the same
     *               order as they appear in the format string. For example:
     *               [
     *                 [givenName, familyName],
     *                 [organization],
     *                 [addressLine1],
     *                 [addressLine2],
     *                 [locality, administrativeArea, postalCode]
     *               ]
     */
    public static function getGroupedFields($formatString)
    {
        $groupedFields = [];
        $expression = '/\%(' . implode('|', AddressField::getAll()) . ')/';
        $formatLines = explode("\n", $formatString);
        foreach ($formatLines as $index => $formatLine) {
            preg_match_all($expression, $formatLine, $foundTokens);
            foreach ($foundTokens[0] as $token) {
                $groupedFields[$index][] = substr($token, 1);
            }
        }
        // The indexes won't be sequential if there were any rows
        // without tokens, so reset them.
        $groupedFields = array_values($groupedFields);

        return $groupedFields;
    }
}
