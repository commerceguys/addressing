<?php

namespace CommerceGuys\Addressing\AddressFormat;

/**
 * Provides helpers for handling address formats.
 */
final class AddressFormatHelper
{
    /**
     * Gets the list of used fields, grouped by line.
     *
     * Used for generating address forms.
     *
     * Applies field overrides, to ensure hidden fields are skipped.
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
     * @throws \ReflectionException
     */
    public static function getGroupedFields(string $formatString, FieldOverrides $fieldOverrides = null): array
    {
        $groupedFields = [];
        $hiddenFields = $fieldOverrides ? $fieldOverrides->getHiddenFields() : [];
        $expression = '/\%(' . implode('|', AddressField::getAll()) . ')/';
        $formatLines = explode("\n", $formatString);
        foreach ($formatLines as $index => $formatLine) {
            preg_match_all($expression, $formatLine, $foundTokens);
            foreach ($foundTokens[0] as $token) {
                $field = substr($token, 1);
                if (!in_array($field, $hiddenFields)) {
                    $groupedFields[$index][] = substr($token, 1);
                }
            }
        }
        // The indexes won't be sequential if there were any rows
        // without tokens, so reset them.
        $groupedFields = array_values($groupedFields);

        return $groupedFields;
    }

    /**
     * Gets the required fields.
     *
     * Applies field overrides to the required fields
     * specified by the address format.
     *
     * @return string[] The required fields.
     */
    public static function getRequiredFields(AddressFormat $addressFormat, FieldOverrides $fieldOverrides): array
    {
        $requiredFields = $addressFormat->getRequiredFields();
        $requiredFields = array_diff($requiredFields, $fieldOverrides->getOptionalFields());
        $requiredFields = array_diff($requiredFields, $fieldOverrides->getHiddenFields());
        if ($fieldOverrides->getRequiredFields()) {
            $requiredFields = array_merge($requiredFields, $fieldOverrides->getRequiredFields());
            $requiredFields = array_unique($requiredFields);
        }

        return $requiredFields;
    }
}
