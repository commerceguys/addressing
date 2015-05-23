<?php

namespace CommerceGuys\Addressing\Validator\Constraints;

use CommerceGuys\Addressing\Repository\CountryRepository;
use CommerceGuys\Addressing\Repository\CountryRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class CountryValidator extends ConstraintValidator
{
    /**
     * The country repository.
     *
     * @var CountryRepositoryInterface
     */
    protected $countryRepository;

    /**
     * Creates a CountryValidator instance.
     *
     * @param CountryRepositoryInterface $countryRepository
     */
    public function __construct(CountryRepositoryInterface $countryRepository = null)
    {
        $this->countryRepository = $countryRepository ?: new CountryRepository();
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if ($value === null || $value === '') {
            return;
        }

        if (!is_scalar($value) && !(is_object($value) && method_exists($value, '__toString'))) {
            throw new UnexpectedTypeException($value, 'string');
        }

        $countries = $this->countryRepository->getList();
        $value = (string) $value;

        if (!isset($countries[$value])) {
            $this->context->addViolation($constraint->message, [
                '{{ value }}' => $this->formatValue($value),
            ]);
        }
    }
}
