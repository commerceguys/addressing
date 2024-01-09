<?php

namespace CommerceGuys\Addressing\Tests\Validator\Constraints;

use CommerceGuys\Addressing\Address;
use CommerceGuys\Addressing\AddressFormat\AddressField;
use CommerceGuys\Addressing\AddressFormat\FieldOverride;
use CommerceGuys\Addressing\AddressFormat\FieldOverrides;
use CommerceGuys\Addressing\Validator\Constraints\AddressFormatConstraint;
use CommerceGuys\Addressing\Validator\Constraints\AddressFormatConstraintValidator;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

/**
 * @coversDefaultClass \CommerceGuys\Addressing\Validator\Constraints\AddressFormatConstraintValidator
 */
final class AddressFormatConstraintValidatorTest extends ConstraintValidatorTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->constraint = new AddressFormatConstraint();

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

        $this->defaultLocale = 'en';

        $this->expectedViolations = [];
        $this->call = 0;

        $this->setDefaultTimezone('UTC');
    }

    protected function tearDown(): void
    {
        $this->restoreDefaultTimezone();
    }

    protected function createValidator(): AddressFormatConstraintValidator
    {
        return new AddressFormatConstraintValidator();
    }

    /**
     * @covers \CommerceGuys\Addressing\Validator\Constraints\AddressFormatConstraintValidator
     */
    public function testInvalidValueType(): void
    {
        $this->expectException(\Symfony\Component\Validator\Exception\UnexpectedTypeException::class);
        $this->validator->validate(new \stdClass(), $this->constraint);
    }

    /**
     * @covers \CommerceGuys\Addressing\Validator\Constraints\AddressFormatConstraintValidator
     */
    public function testEmptyIsValid(): void
    {
        $this->validator->validate(new Address(), $this->constraint);
        $this->assertNoViolation();
    }

    /**
     * @covers \CommerceGuys\Addressing\Validator\Constraints\AddressFormatConstraintValidator
     */
    public function testAndorraOK(): void
    {
        $address = new Address();
        $address = $address
            ->withCountryCode('AD')
            ->withLocality("07")
            ->withPostalCode('AD500')
            ->withAddressLine1('C. Prat de la Creu, 62-64')
            ->withGivenName('Antoni')
            ->withFamilyName('Martí');

        $this->validator->validate($address, $this->constraint);
        $this->assertNoViolation();
    }

    /**
     * @covers \CommerceGuys\Addressing\Validator\Constraints\AddressFormatConstraintValidator
     */
    public function testAndorraNotOK(): void
    {
        // Andorra has no predefined administrative areas, but it does have
        // predefined localities, which must be validated.
        $address = new Address();
        $address = $address
            ->withCountryCode('AD')
            ->withLocality('INVALID')
            ->withPostalCode('AD500')
            ->withAddressLine1('C. Prat de la Creu, 62-64')
            ->withGivenName('Antoni')
            ->withFamilyName('Martí');

        $this->validator->validate($address, $this->constraint);
        $this->buildViolation($this->constraint->invalidMessage)
            ->atPath('[locality]')
            ->setInvalidValue('INVALID')
            ->assertRaised();
    }

    /**
     * @covers \CommerceGuys\Addressing\Validator\Constraints\AddressFormatConstraintValidator
     */
    public function testUnitedStatesOK(): void
    {
        $address = new Address();
        $address = $address
            ->withCountryCode('US')
            ->withAdministrativeArea('CA')
            ->withLocality('Mountain View')
            ->withPostalCode('94043')
            ->withAddressLine1('1098 Alta Ave')
            ->withGivenName('John')
            ->withFamilyName('Smith');

        $this->validator->validate($address, $this->constraint);
        $this->assertNoViolation();
    }

    /**
     * @covers \CommerceGuys\Addressing\Validator\Constraints\AddressFormatConstraintValidator
     */
    public function testUnitedStatesNotOK(): void
    {
        $address = new Address();
        $address = $address
            ->withCountryCode('US')
            ->withAdministrativeArea('CA')
            // Fails the format-level check.
            ->withPostalCode('909')
            ->withGivenName('John')
            ->withFamilyName('Smith');

        $this->validator->validate($address, $this->constraint);
        $this->buildViolation($this->constraint->notBlankMessage)
            ->atPath('[addressLine1]')
            ->setInvalidValue(null)
            ->buildNextViolation($this->constraint->notBlankMessage)
            ->atPath('[locality]')
            ->setInvalidValue(null)
            ->buildNextViolation($this->constraint->invalidMessage)
            ->atPath('[postalCode]')
            ->setInvalidValue('909')
            ->assertRaised();
    }

    /**
     * @covers \CommerceGuys\Addressing\Validator\Constraints\AddressFormatConstraintValidator
     */
    public function testUnitedStatesSubdivisionPostcodePattern(): void
    {
        $address = new Address();
        $address = $address
            ->withCountryCode('US')
            ->withAdministrativeArea('CA')
            ->withLocality('Mountain View')
            ->withAddressLine1('1098 Alta Ave')
            // Satisfies the format-level check, fails the subdivision-level one.
            ->withPostalCode('84025')
            ->withGivenName('John')
            ->withFamilyName('Smith');

        $this->validator->validate($address, $this->constraint);
        $this->assertNoViolation();
    }

    /**
     * @covers \CommerceGuys\Addressing\Validator\Constraints\AddressFormatConstraintValidator
     */
    public function testChinaOK(): void
    {
        $address = new Address();
        $address = $address
            ->withCountryCode('CN')
            ->withAdministrativeArea('BJ')
            ->withLocality('Xicheng Qu')
            ->withPostalCode('123456')
            ->withAddressLine1('Yitiao Lu')
            ->withGivenName('John')
            ->withFamilyName('Smith');

        $this->validator->validate($address, $this->constraint);
        $this->assertNoViolation();
    }

    /**
     * @covers \CommerceGuys\Addressing\Validator\Constraints\AddressFormatConstraintValidator
     */
    public function testGermanAddress(): void
    {
        $address = new Address();
        $address = $address
            ->withCountryCode('DE')
            ->withLocality('Berlin')
            ->withPostalCode('10553')
            ->withAddressLine1('Huttenstr. 50')
            ->withOrganization('BMW AG Niederkassung Berlin')
            ->withGivenName('Dieter')
            ->withFamilyName('Diefendorf');

        $this->validator->validate($address, $this->constraint);
        $this->assertNoViolation();
    }

    /**
     * @covers \CommerceGuys\Addressing\Validator\Constraints\AddressFormatConstraintValidator
     */
    public function testIrishAddress(): void
    {
        $address = new Address();
        $address = $address
            ->withCountryCode('IE')
            ->withAdministrativeArea('DL')
            ->withLocality('Dublin')
            ->withAddressLine1('7424 118 Avenue NW')
            ->withGivenName('Conan')
            ->withFamilyName("O'Brien");

        $this->validator->validate($address, $this->constraint);
        $this->assertNoViolation();
    }

    /**
     * @covers \CommerceGuys\Addressing\Validator\Constraints\AddressFormatConstraintValidator
     */
    public function testChinaPostalCodeBadFormat(): void
    {
        $address = new Address();
        $address = $address
            ->withCountryCode('CN')
            ->withAdministrativeArea('BJ')
            ->withLocality('Xicheng Qu')
            ->withPostalCode('InvalidValue')
            ->withGivenName('John')
            ->withFamilyName('Smith');

        $this->validator->validate($address, $this->constraint);
        $this->buildViolation($this->constraint->notBlankMessage)
            ->atPath('[addressLine1]')
            ->setInvalidValue(null)
            ->buildNextViolation($this->constraint->invalidMessage)
            ->atPath('[postalCode]')
            ->assertRaised();
    }

    /**
     * @covers \CommerceGuys\Addressing\Validator\Constraints\AddressFormatConstraintValidator
     */
    public function testEmptyPostalCodeReportedAsGoodFormat(): void
    {
        $address = new Address();
        $address = $address
            ->withCountryCode('CL')
            ->withAdministrativeArea('AN')
            ->withLocality('San Pedro de Atacama')
            ->withPostalCode('')
            ->withAddressLine1('GUSTAVO LE PAIGE ST #159')
            ->withGivenName('John')
            ->withFamilyName('Smith');

        $this->validator->validate($address, $this->constraint);
        $this->assertNoViolation();

        // Now check for US addresses, which require a postal code. The following
        // address's postal code is wrong because it is missing a required field, not
        // because it doesn't match the expected postal code pattern.
        $address = new Address();
        $address = $address
            ->withCountryCode('US')
            ->withAdministrativeArea('CA')
            ->withLocality('California')
            ->withAddressLine1('1098 Alta Ave')
            ->withGivenName('John')
            ->withFamilyName('Smith');

        $this->validator->validate($address, $this->constraint);
        $this->buildViolation($this->constraint->notBlankMessage)
            ->atPath('[postalCode]')
            ->setInvalidValue(null)
            ->assertRaised();
    }

    /**
     * @covers \CommerceGuys\Addressing\Validator\Constraints\AddressFormatConstraintValidator
     */
    public function testChinaTaiwanOk(): void
    {
        $address = new Address();
        $address = $address
            ->withCountryCode('CN')
            ->withAdministrativeArea('TW')
            ->withLocality('Taichung City')
            ->withDependentLocality('Xitun District')
            ->withPostalCode('407')
            ->withAddressLine1('12345 Yitiao Lu')
            ->withGivenName('John')
            ->withFamilyName('Smith');

        $this->validator->validate($address, $this->constraint);
        $this->assertNoViolation();
    }

    /**
     * @covers \CommerceGuys\Addressing\Validator\Constraints\AddressFormatConstraintValidator
     */
    public function testChinaTaiwanUnknownDistrict(): void
    {
        $address = new Address();
        $address = $address
            ->withCountryCode('CN')
            ->withAdministrativeArea('TW')
            ->withLocality('Taichung City')
            ->withDependentLocality('InvalidValue')
            ->withPostalCode('407')
            ->withAddressLine1('12345 Yitiao Lu')
            ->withGivenName('John')
            ->withFamilyName('Smith');

        $this->validator->validate($address, $this->constraint);
        $this->buildViolation($this->constraint->invalidMessage)
            ->atPath('[dependentLocality]')
            ->assertRaised();
    }

    /**
     * @covers \CommerceGuys\Addressing\Validator\Constraints\AddressFormatConstraintValidator
     */
    public function testStreetVerification(): void
    {
        $address = new Address();
        $address = $address
            ->withCountryCode('US')
            ->withAdministrativeArea('CA')
            ->withLocality('Mountain View')
            ->withPostalCode('94043')
            ->withGivenName('John')
            ->withFamilyName('Smith');

        $this->validator->validate($address, $this->constraint);
        $this->buildViolation($this->constraint->notBlankMessage)
            ->atPath('[addressLine1]')
            ->setInvalidValue(null)
            ->assertRaised();
    }

    /**
     * @covers \CommerceGuys\Addressing\Validator\Constraints\AddressFormatConstraintValidator
     */
    public function testJapan(): void
    {
        $address = new Address();
        $address = $address
            ->withCountryCode('JP')
            ->withAdministrativeArea('26')
            ->withLocality('Shigeru Miyamoto')
            ->withPostalCode('601-8501')
            ->withAddressLine1('11-1 Kamitoba-hokotate-cho')
            ->withGivenName('John')
            ->withFamilyName('Smith');

        $this->validator->validate($address, $this->constraint);
        $this->assertNoViolation();
    }

    /**
     * @covers \CommerceGuys\Addressing\Validator\Constraints\AddressFormatConstraintValidator
     */
    public function testCanadaMixedCasePostcode(): void
    {
        $address = new Address();
        $address = $address
            ->withCountryCode('CA')
            ->withAdministrativeArea('QC')
            ->withLocality('Montreal')
            ->withPostalCode('H2b 2y5')
            ->withAddressLine1('11 East St')
            ->withGivenName('Joe')
            ->withFamilyName('Bloggs');

        $this->validator->validate($address, $this->constraint);
        $this->assertNoViolation();
    }

    /**
     * @covers \CommerceGuys\Addressing\Validator\Constraints\AddressFormatConstraintValidator
     */
    public function testCanadaUnusedFields(): void
    {
        $address = new Address();
        $address = $address
            ->withCountryCode('CA')
            ->withAdministrativeArea('QC')
            ->withLocality('Montreal')
            ->withPostalCode('H2b 2y5')
            ->withSortingCode('InvalidValue')
            ->withAddressLine1('11 East St')
            ->withGivenName('Joe')
            ->withFamilyName('Bloggs');

        $this->validator->validate($address, $this->constraint);
        $this->buildViolation($this->constraint->blankMessage)
            ->atPath('[sortingCode]')
            ->assertRaised();
    }

    /**
     * @covers \CommerceGuys\Addressing\Validator\Constraints\AddressFormatConstraintValidator
     */
    public function testOverriddenRequiredFields(): void
    {
        // Confirm that it is possible to omit required name fields.
        $address = new Address();
        $address = $address
            ->withCountryCode('CN')
            ->withAdministrativeArea('BJ')
            ->withLocality('Xicheng Qu')
            ->withPostalCode('123456')
            ->withAddressLine1('Yitiao Lu');

        $this->constraint->fieldOverrides = new FieldOverrides([
            AddressField::GIVEN_NAME => FieldOverride::OPTIONAL,
            AddressField::FAMILY_NAME => FieldOverride::OPTIONAL,
        ]);
        $this->validator->validate($address, $this->constraint);
        $this->assertNoViolation();
    }

    /**
     * @covers \CommerceGuys\Addressing\Validator\Constraints\AddressFormatConstraintValidator
     */
    public function testHiddenPostalCodeField(): void
    {
        // Confirm that postal code validation is skipped.
        $this->constraint->fieldOverrides = new FieldOverrides([
            AddressField::POSTAL_CODE => FieldOverride::HIDDEN,
        ]);
        $address = new Address();
        $address = $address
            ->withCountryCode('CN')
            ->withAdministrativeArea('BJ')
            ->withLocality('Xicheng Qu')
            ->withAddressLine1('Yitiao Lu')
            ->withGivenName('John')
            ->withFamilyName('Smith')
            ->withPostalCode('INVALID');
        $this->validator->validate($address, $this->constraint);
        $this->buildViolation($this->constraint->blankMessage)
            ->atPath('[postalCode]')
            ->setInvalidValue('INVALID')
            ->assertRaised();
    }

    /**
     * @covers \CommerceGuys\Addressing\Validator\Constraints\AddressFormatConstraintValidator
     */
    public function testHiddenSubdivisionField(): void
    {
        // Confirm that subdivision validation is skipped.
        $this->constraint->fieldOverrides = new FieldOverrides([
            AddressField::ADMINISTRATIVE_AREA => FieldOverride::HIDDEN,
        ]);
        $address = new Address();
        $address = $address
            ->withCountryCode('CN')
            ->withLocality('Xicheng Qu')
            ->withPostalCode('123456')
            ->withAddressLine1('Yitiao Lu')
            ->withGivenName('John')
            ->withFamilyName('Smith')
            ->withAdministrativeArea('INVALID');
        $this->validator->validate($address, $this->constraint);
        $this->buildViolation($this->constraint->blankMessage)
            ->atPath('[administrativeArea]')
            ->setInvalidValue('INVALID')
            ->assertRaised();
    }

    /**
     * @covers \CommerceGuys\Addressing\Validator\Constraints\AddressFormatConstraintValidator
     */
    public function testNoPostalCodeValidation(): void
    {
        // Confirm that postal code validation is skipped.
        $this->constraint->validatePostalCode = false;
        $address = new Address();
        $address = $address
            ->withCountryCode('CN')
            ->withAdministrativeArea('BJ')
            ->withLocality('Xicheng Qu')
            ->withAddressLine1('Yitiao Lu')
            ->withGivenName('John')
            ->withFamilyName('Smith')
            ->withPostalCode('INVALID');
        $this->validator->validate($address, $this->constraint);
        $this->assertNoViolation();
    }
}
