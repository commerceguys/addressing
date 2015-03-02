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
        foreach ($requiredFields as $field) {
            if (empty($values[$field])) {
                $this->context->addViolationAt('[' . $field . ']', $constraint->notBlankMessage, [], $values[$field]);
            }
        }

        // Validate the absence of unused fields.
        $unusedFields = array_diff(AddressField::getAll(), $addressFormat->getUsedFields());
        foreach ($unusedFields as $field) {
            if (!empty($values[$field])) {
                $this->context->addViolationAt('[' . $field . ']', $constraint->blankMessage, [], $values[$field]);
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
        foreach ($subdivisionLevels as $index => $field) {
            $parentId = ($field == 'root') ? 0 : $values[$field];
            $children = $dataProvider->getSubdivisions($countryCode, $parentId);
            $nextIndex = $index + 1;
            if (!$children || !isset($subdivisionLevels[$nextIndex])) {
                // This level has no children, stop.
                break;
            }
            $nextField = $subdivisionLevels[$nextIndex];
            if (empty($values[$nextField])) {
                // The child value is empty, stop.
                break;
            }

            $found = false;
            foreach ($children as $child) {
                if ($child->getId() == $values[$nextField]) {
                    $found = true;
                    $subdivisions[] = $child;
                    break;
                }
            }

            if (!$found) {
                $this->context->addViolationAt('[' . $nextField . ']', $constraint->invalidMessage, [], $values[$nextField]);
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
        foreach (AddressField::getAll() as $field) {
            $getter = 'get' . ucfirst($field);
            $values[$field] = $address->$getter();
        }

        return $values;
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
