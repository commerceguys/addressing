<?php

namespace CommerceGuys\Addressing\Tests\Validator\Constraints;

use CommerceGuys\Addressing\Validator\Constraints\CountryConstraint;
use CommerceGuys\Addressing\Validator\Constraints\CountryConstraintValidator;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

/**
 * @coversDefaultClass \CommerceGuys\Addressing\Validator\Constraints\CountryConstraintValidator
 */
final class CountryConstraintValidatorTest extends ConstraintValidatorTestCase
{
    /**
     * @var CountryConstraint
     */
    protected $constraint;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->constraint = new CountryConstraint();

        // The following code is copied from the parent setUp(), which isn't
        // called to avoid the call to \Locale, which introduces a dependency
        // on the intl extension (or symfony/intl).
        $this->group = 'MyGroup';
        $this->metadata = null;
        $this->object = null;
        $this->value = 'InvalidValue';
        $this->root = 'root';
        $this->propertyPath = '';
        $this->context = $this->createContext();
        $this->validator = $this->createValidator();
        $this->validator->initialize($this->context);
    }

    protected function createValidator()
    {
        return new CountryConstraintValidator();
    }

    /**
     * @covers \CommerceGuys\Addressing\Validator\Constraints\CountryConstraintValidator
     */
    public function testEmptyIsValid()
    {
        $this->validator->validate(null, $this->constraint);
        $this->assertNoViolation();

        $this->validator->validate('', $this->constraint);
        $this->assertNoViolation();
    }

    /**
     * @covers \CommerceGuys\Addressing\Validator\Constraints\CountryConstraintValidator
     *
     *
     */
    public function testInvalidValueType()
    {
        $this->expectException(\Symfony\Component\Validator\Exception\UnexpectedTypeException::class);
        $this->validator->validate(new \stdClass(), $this->constraint);
    }

    /**
     * @covers \CommerceGuys\Addressing\Validator\Constraints\CountryConstraintValidator
     */
    public function testInvalidCountry()
    {
        $this->validator->validate('InvalidValue', $this->constraint);
        $this->buildViolation($this->constraint->message)
            ->setParameters(['{{ value }}' => '"InvalidValue"'])
            ->atPath('')
            ->assertRaised();
    }

    /**
     * @covers \CommerceGuys\Addressing\Validator\Constraints\CountryConstraintValidator
     *
     * @dataProvider getValidCountries
     */
    public function testValidCountries($country)
    {
        $this->validator->validate($country, $this->constraint);
        $this->assertNoViolation();
    }

    public function getValidCountries(): array
    {
        return [
            ['GB'],
            ['AT'],
            ['MY'],
        ];
    }
}
