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

        $dataProvider = $this->getDataProvider();
        $countries = $dataProvider->getCountryNames();
        $value = (string) $value;

        if (!isset($countries[$value])) {
            $this->context->addViolation($constraint->message, [
                '{{ value }}' => $this->formatValue($value),
            ]);
        }
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
