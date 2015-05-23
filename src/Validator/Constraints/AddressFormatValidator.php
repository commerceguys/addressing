<?php

namespace CommerceGuys\Addressing\Validator\Constraints;

use CommerceGuys\Addressing\Enum\AddressField;
use CommerceGuys\Addressing\Enum\PatternType;
use CommerceGuys\Addressing\Model\AddressInterface;
use CommerceGuys\Addressing\Model\AddressFormatInterface;
use CommerceGuys\Addressing\Repository\AddressFormatRepository;
use CommerceGuys\Addressing\Repository\AddressFormatRepositoryInterface;
use CommerceGuys\Addressing\Repository\SubdivisionRepository;
use CommerceGuys\Addressing\Repository\SubdivisionRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class AddressFormatValidator extends ConstraintValidator
{
    /**
     * The address format repository.
     *
     * @var AddressFormatRepositoryInterface
     */
    protected $addressFormatRepository;

    /**
     * The subdivision repository.
     *
     * @var SubdivisionRepositoryInterface
     */
    protected $subdivisionRepository;

    /**
     * Creates an AddressFormatValidator instance.
     *
     * @param AddressFormatRepositoryInterface $addressFormatRepository
     * @param SubdivisionRepositoryInterface   $subdivisionRepository
     */
    public function __construct(AddressFormatRepositoryInterface $addressFormatRepository = null, SubdivisionRepositoryInterface $subdivisionRepository = null)
    {
        $this->addressFormatRepository = $addressFormatRepository ?: new AddressFormatRepository();
        $this->subdivisionRepository = $subdivisionRepository ?: new SubdivisionRepository();
    }

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
        $addressFormat = $this->addressFormatRepository->get($address->getCountryCode());

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
        $countryCode = $addressFormat->getCountryCode();
        $subdivisionLevels = [
            'root',
            AddressField::ADMINISTRATIVE_AREA,
            AddressField::LOCALITY,
            AddressField::DEPENDENT_LOCALITY,
        ];
        $foundIds = [];
        foreach ($subdivisionLevels as $index => $field) {
            $parentId = ($field == 'root') ? 0 : $values[$field];
            $children = $this->subdivisionRepository->getList($countryCode, $parentId);
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
            $nextValue = $values[$nextField];
            if (isset($children[$nextValue])) {
                $found = true;
                $foundIds[] = $nextValue;
            }

            if (!$found) {
                $this->context->addViolationAt('[' . $nextField . ']', $constraint->invalidMessage, [], $values[$nextField]);
                break;
            }
        }

        // Load the found subdivision ids.
        $subdivisions = [];
        foreach ($foundIds as $id) {
            $subdivisions[] = $this->subdivisionRepository->get($id);
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

        // Resolve the available patterns.
        $fullPattern = $addressFormat->getPostalCodePattern();
        $startPattern = null;
        foreach ($subdivisions as $subdivision) {
            $pattern = $subdivision->getPostalCodePattern();
            if (empty($pattern)) {
                continue;
            }

            if ($subdivision->getPostalCodePatternType() == PatternType::FULL) {
                $fullPattern = $pattern;
            } else {
                $startPattern = $pattern;
            }
        }

        if ($fullPattern) {
            // The pattern must match the provided value completely.
            preg_match('/' . $fullPattern . '/i', $postalCode, $matches);
            if (empty($matches[0]) || $matches[0] != $postalCode) {
                $this->context->addViolationAt('[postalCode]', $constraint->invalidMessage, [], $postalCode);

                return;
            }
        }
        if ($startPattern) {
            // The pattern must match the start of the provided value.
            preg_match('/' . $startPattern . '/i', $postalCode, $matches);
            if (empty($matches[0]) || strpos($postalCode, $matches[0]) !== 0) {
                $this->context->addViolationAt('[postalCode]', $constraint->invalidMessage, [], $postalCode);

                return;
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
}
