<?php

namespace CommerceGuys\Addressing\Model;

use CommerceGuys\Addressing\Enum\AddressField;

/**
 * Provides the format string with getters, setters and introspection methods.
 *
 * Allows external systems to reimplement AddressFormat without needing
 * to duplicate the format introspection logic.
 *
 * @see AddressFormatEntityInterface
 */
trait FormatStringTrait
{
    /**
     * The format string.
     *
     * @var string
     */
    protected $format;

    /**
     * The used fields.
     *
     * @var array
     */
    protected $usedFields;

    /**
     * The used fields, grouped by line.
     *
     * @var array
     */
    protected $groupedFields;

    /**
     * {@inheritdoc}
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * {@inheritdoc}
     */
    public function setFormat($format)
    {
        $this->format = $format;
        // Reset the cached field metadata derived from the format string.
        $this->usedFields = null;
        $this->groupedFields = null;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsedFields()
    {
        if (!isset($this->usedFields)) {
            $this->usedFields = [];
            foreach (AddressField::getAll() as $field) {
                if (strpos($this->format, '%' . $field) !== false) {
                    $this->usedFields[] = $field;
                }
            }
        }

        return $this->usedFields;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsedSubdivisionFields()
    {
        $fields = [
            AddressField::ADMINISTRATIVE_AREA,
            AddressField::LOCALITY,
            AddressField::DEPENDENT_LOCALITY,
        ];
        // Remove fields not used by the format, and reset the keys.
        $fields = array_intersect($fields, $this->getUsedFields());
        $fields = array_values($fields);

        return $fields;
    }

    /**
     * {@inheritdoc}
     */
    public function getGroupedFields()
    {
        if (!isset($this->groupedFields)) {
            $this->groupedFields = [];
            $expression = '/\%(' . implode('|', AddressField::getAll()) . ')/';
            $formatLines = explode("\n", $this->format);
            foreach ($formatLines as $index => $formatLine) {
                preg_match_all($expression, $formatLine, $foundTokens);
                foreach ($foundTokens[0] as $token) {
                    $this->groupedFields[$index][] = substr($token, 1);
                }
            }
            // The indexes won't be sequential if there were any rows
            // without tokens, so reset them.
            $this->groupedFields = array_values($this->groupedFields);
        }

        return $this->groupedFields;
    }
}
