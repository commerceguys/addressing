<?php

namespace CommerceGuys\Addressing\Validator\Constraints;

use CommerceGuys\Addressing\Enum\AddressField;
use CommerceGuys\Addressing\Model\AddressInterface;
use CommerceGuys\Addressing\Model\AddressFormatInterface;
use CommerceGuys\Addressing\Provider\DataProvider;
use CommerceGuys\Addressing\Provider\DataProviderInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class AddressFormatValidator extends ConstraintValidator
{
    /**
     * The data provider.
     *
     * @var DataProviderInterface
     */
    protected $dataProvider;

    /**
     * The mapping between field constants (format) and field names (address).
     *
     * @var array
     */
    protected $fieldMapping = [
        AddressField::ADMINISTRATIVE_AREA => 'administrativeArea',
        AddressField::LOCALITY => 'locality',
        AddressField::DEPENDENT_LOCALITY => 'dependentLocality',
        AddressField::POSTAL_CODE => 'postalCode',
        AddressField::SORTING_CODE => 'sortingCode',
        AddressField::ADDRESS => 'addressLine1',
        AddressField::ORGANIZATION => 'organization',
        AddressField::RECIPIENT => 'recipient',
    ];

    /**
     * {@inheritDoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!($value instanceof AddressInterface)) {
            throw new UnexpectedTypeException($value, 'AddressInterface');
        }

        $address = $value;
        $countryCode = $address->getCountryCode();
        if ($countryCode === null || $countryCode === '') {
            return;
        }

        $values = $this->extractAddressValues($address);
        $dataProvider = $this->getDataProvider();
        $addressFormat = $dataProvider->getAddressFormat($address->getCountryCode());

        $this->validateFields($values, $addressFormat, $constraint);
        $subdivisions = $this->validateSubdivisions($values, $addressFormat, $constraint);
        $this->validatePostalCode($address->getPostalCode(), $subdivisions, $addressFormat, $constraint);
    }

    /**
     * Validates the provided field values.
     *
     * @param array                  $values        The field values, keyed by field constants.
     * @param AddressFormatInterface $addressFormat The address format.
     * @param Constraint             $constraint    The constraint.
     */
    protected function validateFields($values, AddressFormatInterface $addressFormat, $constraint)
    {
        // Validate the presence of required fields.
        $requiredFields = $addressFormat->getRequiredFields();
        foreach ($requiredFields as $fieldConstant) {
            if (empty($values[$fieldConstant])) {
                $subPath = '[' . $this->fieldMapping[$fieldConstant] . ']';
                $this->context->addViolationAt($subPath, $constraint->notBlankMessage, [], $values[$fieldConstant]);
            }
        }

        // Validate the absence of unused fields.
        $unusedFields = $this->getUnusedFields($addressFormat->getFormat());
        foreach ($unusedFields as $fieldConstant) {
            if (!empty($values[$fieldConstant])) {
                $subPath = '[' . $this->fieldMapping[$fieldConstant] . ']';
                $this->context->addViolationAt($subPath, $constraint->blankMessage, [], $values[$fieldConstant]);
            }
        }
    }

    /**
     * Validates the provided subdivision values.
     *
     * @param array                  $values        The field values, keyed by field constants.
     * @param AddressFormatInterface $addressFormat The address format.
     * @param Constraint             $constraint    The constraint.
     *
     * @return array An array of found valid subdivisions.
     */
    protected function validateSubdivisions($values, AddressFormatInterface $addressFormat, $constraint)
    {
        $dataProvider = $this->getDataProvider();
        $countryCode = $addressFormat->getCountryCode();
        $subdivisionLevels = [
            'root',
            AddressField::ADMINISTRATIVE_AREA,
            AddressField::LOCALITY,
            AddressField::DEPENDENT_LOCALITY,
        ];
        $subdivisions = [];
        foreach ($subdivisionLevels as $index => $fieldConstant) {
            $parentId = ($fieldConstant == 'root') ? 0 : $values[$fieldConstant];
            $children = $dataProvider->getSubdivisions($countryCode, $parentId);
            $nextIndex = $index + 1;
            if (!$children || !isset($subdivisionLevels[$nextIndex])) {
                // This level has no children, stop.
                break;
            }
            $nextFieldConstant = $subdivisionLevels[$nextIndex];
            if (empty($values[$nextFieldConstant])) {
                // The child value is empty, stop.
                break;
            }

            $found = false;
            foreach ($children as $child) {
                if ($child->getId() == $values[$nextFieldConstant]) {
                    $found = true;
                    $subdivisions[] = $child;
                    break;
                }
            }

            if (!$found) {
                $subPath = '[' . $this->fieldMapping[$nextFieldConstant] . ']';
                $invalidValue = $values[$nextFieldConstant];
                $this->context->addViolationAt($subPath, $constraint->invalidMessage, [], $invalidValue);
                break;
            }
        }

        return $subdivisions;
    }

    /**
     * Validates the provided postal code.
     *
     * @param string                 $postalCode    The postal code.
     * @param array                  $subdivisions  An array of found valid subdivisions.
     * @param AddressFormatInterface $addressFormat The address format.
     * @param Constraint             $constraint    The constraint.
     */
    protected function validatePostalCode($postalCode, array $subdivisions, AddressFormatInterface $addressFormat, $constraint)
    {
        if (empty($postalCode)) {
            // Nothing to validate.
            return;
        }

        // Try to find a postal code pattern.
        $subdivisionPostalCodePattern = null;
        if ($subdivisions) {
            foreach ($subdivisions as $subdivision) {
                if ($subdivision->getPostalCodePattern()) {
                    $subdivisionPostalCodePattern = '/' . $subdivision->getPostalCodePattern() . '/';
                }
            }
        }

        if ($subdivisionPostalCodePattern) {
            // The subdivision pattern must be a partial match, it only
            // confirms that the value starts with the expected characters.
            if (!preg_match($subdivisionPostalCodePattern, $postalCode)) {
                $this->context->addViolationAt('[postalCode]', $constraint->invalidMessage, [], $postalCode);
            }
        } else {
            preg_match('/' . $addressFormat->getPostalCodePattern() . '/', $postalCode, $matches);
            // The pattern must match the provided value completely.
            if (empty($matches[0]) || $matches[0] != $postalCode) {
                $this->context->addViolationAt('[postalCode]', $constraint->invalidMessage, [], $postalCode);
            }
        }
    }

    /**
     * Extracts the address values.
     *
     * @param AddressInterface $address The address.
     *
     * @return array An array of values keyed by field constants.
     */
    protected function extractAddressValues(AddressInterface $address)
    {
        $values = [];
        foreach ($this->fieldMapping as $fieldConstant => $fieldName) {
            $getter = 'get' . ucfirst($fieldName);
            $values[$fieldConstant] = $address->$getter();
        }

        return $values;
    }

    /**
     * Gets the list of unused fields.
     *
     * @param string $format The format string to analyze.
     *
     * @return array An array of field constants.
     */
    protected function getUnusedFields($format)
    {
        $unusedFields = [];
        foreach (array_keys($this->fieldMapping) as $fieldConstant) {
            if (strpos($format, $fieldConstant) === false) {
                $unusedFields[] = $fieldConstant;
            }
        }

        return $unusedFields;
    }

    /**
     * Gets the data provider.
     *
     * @return DataProviderInterface The data provider.
     */
    public function getDataProvider()
    {
        if (!$this->dataProvider) {
            $this->dataProvider = new DataProvider();
        }

        return $this->dataProvider;
    }

    /**
     * Sets the data provider.
     *
     * @param DataProviderInterface $dataProvider The data provider.
     */
    public function setDataProvider(DataProviderInterface $dataProvider)
    {
        $this->dataProvider = $dataProvider;
    }
}
