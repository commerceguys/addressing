<?php

namespace CommerceGuys\Addressing\Validator\Constraints;

use CommerceGuys\Addressing\AddressInterface;
use CommerceGuys\Addressing\Metadata\AddressFormatInterface;
use CommerceGuys\Addressing\Metadata\AddressMetadataRepository;
use CommerceGuys\Addressing\Metadata\AddressMetadataRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class AddressFormatValidator extends ConstraintValidator
{
    /**
     * The metadata repository.
     *
     * @var AddressMetadataRepositoryInterface
     */
    protected $repository;

    /**
     * The mapping between field constants (format) and field names (address).
     *
     * @var array
     */
    protected $fieldMapping = array(
        AddressFormatInterface::FIELD_ADMINISTRATIVE_AREA => 'administrativeArea',
        AddressFormatInterface::FIELD_LOCALITY => 'locality',
        AddressFormatInterface::FIELD_DEPENDENT_LOCALITY => 'dependentLocality',
        AddressFormatInterface::FIELD_POSTAL_CODE => 'postalCode',
        AddressFormatInterface::FIELD_SORTING_CODE => 'sortingCode',
        AddressFormatInterface::FIELD_ADDRESS => 'addressLine1',
        AddressFormatInterface::FIELD_ORGANIZATION => 'organization',
        AddressFormatInterface::FIELD_RECIPIENT => 'recipient',
    );

    /**
     * {@inheritDoc}
     */
    public function validate($value, Constraint $constraint)
    {
        $address = $value;
        $values = $this->extractAddressValues($address);
        $repository = $this->getRepository();
        $addressFormat = $repository->getAddressFormat($address->getCountryCode());

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
                $this->context->addViolationAt($subPath, $constraint->notBlankMessage, array(), $values[$fieldConstant]);
            }
        }

        // Validate the absence of unused fields.
        $unusedFields = $this->getUnusedFields($addressFormat->getFormat());
        foreach ($unusedFields as $fieldConstant) {
            if (!empty($values[$fieldConstant])) {
                $subPath = '[' . $this->fieldMapping[$fieldConstant] . ']';
                $this->context->addViolationAt($subPath, $constraint->blankMessage, array(), $values[$fieldConstant]);
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
        $repository = $this->getRepository();
        $countryCode = $addressFormat->getCountryCode();
        $subdivisionLevels = array(
            'root',
            AddressFormatInterface::FIELD_ADMINISTRATIVE_AREA,
            AddressFormatInterface::FIELD_LOCALITY,
            AddressFormatInterface::FIELD_DEPENDENT_LOCALITY,
        );
        $subdivisions = array();
        foreach ($subdivisionLevels as $index => $fieldConstant) {
            $parentId = 0;
            if ($fieldConstant != 'root') {
                $parentId = $values[$fieldConstant];
                if (empty($parentId)) {
                    // This level is empty, hence there can be no sublevels, stop.
                    break;
                }
            }

            $children = $repository->getSubdivisions($countryCode, $parentId);
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
                $this->context->addViolationAt($subPath, $constraint->invalidMessage, array(), $invalidValue);
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
                $this->context->addViolationAt('[postalCode]', $constraint->invalidMessage, array(), $postalCode);
            }
        } else {
            preg_match('/' . $addressFormat->getPostalCodePattern() . '/', $postalCode, $matches);
            // The pattern must match the provided value completely.
            if (empty($matches[0]) || $matches[0] != $postalCode) {
                $this->context->addViolationAt('[postalCode]', $constraint->invalidMessage, array(), $postalCode);
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
        $values = array();
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
        $unusedFields = array();
        foreach (array_keys($this->fieldMapping) as $fieldConstant) {
            if (strpos($format, $fieldConstant) === false) {
                $unusedFields[] = $fieldConstant;
            }
        }

        return $unusedFields;
    }

    /**
     * Gets the metadata repository.
     *
     * @return AddressMetadataRepositoryInterface The metadata repository.
     */
    public function getRepository()
    {
        if (!$this->repository) {
            $this->repository = new AddressMetadataRepository();
        }

        return $this->repository;
    }

    /**
     * Sets the metadata repository.
     *
     * @param AddressMetadataRepositoryInterface $repository The metadata repository.
     */
    public function setRepository(AddressMetadataRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }
}
