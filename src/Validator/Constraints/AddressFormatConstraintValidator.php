<?php

namespace CommerceGuys\Addressing\Validator\Constraints;

use CommerceGuys\Addressing\AddressFormat\AddressFormatHelper;
use CommerceGuys\Addressing\AddressInterface;
use CommerceGuys\Addressing\AddressFormat\AddressField;
use CommerceGuys\Addressing\AddressFormat\AddressFormat;
use CommerceGuys\Addressing\AddressFormat\AddressFormatRepository;
use CommerceGuys\Addressing\AddressFormat\AddressFormatRepositoryInterface;
use CommerceGuys\Addressing\Subdivision\PatternType;
use CommerceGuys\Addressing\Subdivision\SubdivisionRepository;
use CommerceGuys\Addressing\Subdivision\SubdivisionRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class AddressFormatConstraintValidator extends ConstraintValidator
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
     */
    public function __construct(AddressFormatRepositoryInterface $addressFormatRepository = null, SubdivisionRepositoryInterface $subdivisionRepository = null)
    {
        $this->addressFormatRepository = $addressFormatRepository ?: new AddressFormatRepository();
        $this->subdivisionRepository = $subdivisionRepository ?: new SubdivisionRepository();
    }

    /**
     * {@inheritdoc}
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

        /** @var AddressFormatConstraint $constraint */
        $fieldOverrides = $constraint->fieldOverrides;
        $addressFormat = $this->addressFormatRepository->get($countryCode);
        $usedFields = array_diff($addressFormat->getUsedFields(), $fieldOverrides->getHiddenFields());
        $values = $this->extractAddressValues($address);

        // Validate the presence of required fields.
        $requiredFields = AddressFormatHelper::getRequiredFields($addressFormat, $fieldOverrides);
        foreach ($requiredFields as $field) {
            if (empty($values[$field])) {
                $this->addViolation($field, $constraint->notBlankMessage, $values[$field], $addressFormat);
            }
        }

        // Validate the absence of unused fields.
        $unusedFields = array_diff(AddressField::getAll(), $usedFields);
        foreach ($unusedFields as $field) {
            if (!empty($values[$field])) {
                $this->addViolation($field, $constraint->blankMessage, $values[$field], $addressFormat);
            }
        }

        // Validate subdivisions and the postal code.
        $subdivisions = $this->validateSubdivisions($values, $addressFormat, $constraint);
        if (in_array(AddressField::POSTAL_CODE, $usedFields)) {
            $this->validatePostalCode($address->getPostalCode(), $subdivisions, $addressFormat, $constraint);
        }
    }

    /**
     * Validates the provided subdivision values.
     *
     * @param array $values        The field values, keyed by field constants.
     * @param AddressFormat           $addressFormat The address format.
     * @param AddressFormatConstraint $constraint    The constraint.
     *
     * @return array An array of found valid subdivisions.
     */
    protected function validateSubdivisions(array $values, AddressFormat $addressFormat, AddressFormatConstraint $constraint): array
    {
        if ($addressFormat->getSubdivisionDepth() < 1) {
            // No predefined subdivisions exist, nothing to validate against.
            return [];
        }

        $countryCode = $addressFormat->getCountryCode();
        $subdivisionFields = $addressFormat->getUsedSubdivisionFields();
        $hiddenFields = $constraint->fieldOverrides->getHiddenFields();
        $parents = [];
        $subdivisions = [];
        foreach ($subdivisionFields as $index => $field) {
            if (empty($values[$field]) || in_array($field, $hiddenFields)) {
                // The field is empty or validation is disabled.
                break;
            }
            $parents[] = $index ? $values[$subdivisionFields[$index - 1]] : $countryCode;
            $subdivision = $this->subdivisionRepository->get($values[$field], $parents);
            if (!$subdivision) {
                $this->addViolation($field, $constraint->invalidMessage, $values[$field], $addressFormat);
                break;
            }

            $subdivisions[] = $subdivision;
            if (!$subdivision->hasChildren()) {
                // No predefined subdivisions below this level, stop here.
                break;
            }
        }

        return $subdivisions;
    }

    protected function validatePostalCode(string $postalCode, array $subdivisions, AddressFormat $addressFormat, AddressFormatConstraint $constraint): void
    {
        if (empty($postalCode)) {
            // Nothing to validate.
            return;
        }

        // Resolve the available patterns.
        $fullPattern = $addressFormat->getPostalCodePattern();
        $startPattern = null;
        if (!empty($constraint->extendedPostalCodeValidation)) {
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
        }

        if ($fullPattern) {
            // The pattern must match the provided value completely.
            preg_match('/' . $fullPattern . '/i', $postalCode, $matches);
            if (!isset($matches[0]) || $matches[0] !== $postalCode) {
                $this->addViolation(AddressField::POSTAL_CODE, $constraint->invalidMessage, $postalCode, $addressFormat);

                return;
            }
        }
        if ($startPattern) {
            // The pattern must match the start of the provided value.
            preg_match('/' . $startPattern . '/i', $postalCode, $matches);
            if (!isset($matches[0]) || strpos($postalCode, $matches[0]) !== 0) {
                $this->addViolation(AddressField::POSTAL_CODE, $constraint->invalidMessage, $postalCode, $addressFormat);

                return;
            }
        }
    }

    /**
     * Adds a violation.
     *
     * @param string $message        The error message.
     * @param mixed  $invalidValue   The invalid, validated value.
     */
    protected function addViolation(string $field, string $message, $invalidValue, AddressFormat $addressFormat)
    {
        $this->context->buildViolation($message)
            ->atPath('[' . $field . ']')
            ->setInvalidValue($invalidValue)
            ->addViolation();
    }

    /**
     * Extracts the address values.
     *
     * @param AddressInterface $address The address.
     *
     * @return array An array of values keyed by field constants.
     */
    protected function extractAddressValues(AddressInterface $address): array
    {
        $values = [];
        foreach (AddressField::getAll() as $field) {
            $getter = 'get' . ucfirst($field);
            $values[$field] = $address->$getter();
        }

        return $values;
    }
}
