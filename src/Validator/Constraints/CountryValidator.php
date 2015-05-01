<?php

namespace CommerceGuys\Addressing\Validator\Constraints;

use CommerceGuys\Addressing\Provider\DataProvider;
use CommerceGuys\Addressing\Provider\DataProviderInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class CountryValidator extends ConstraintValidator
{
    /**
     * The data provider.
     *
     * @var DataProviderInterface
     */
    protected $dataProvider;

    /**
     * Creates a CountryValidator instance.
     *
     * @param DataProviderInterface $dataProvider
     */
    public function __construct(DataProviderInterface $dataProvider = null)
    {
        $this->dataProvider = $dataProvider ? $dataProvider : new DataProvider();
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

        $countries = $this->dataProvider->getCountryNames();
        $value = (string) $value;

        if (!isset($countries[$value])) {
            $this->context->addViolation($constraint->message, [
                '{{ value }}' => $this->formatValue($value),
            ]);
        }
    }
}
