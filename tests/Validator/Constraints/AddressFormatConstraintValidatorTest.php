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
     * @var AddressFormatConstraint
     */
    protected $constraint;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
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
    }

    protected function createValidator()
    {
        return new AddressFormatConstraintValidator();
    }

    /**
     * @covers \CommerceGuys\Addressing\Validator\Constraints\AddressFormatConstraintValidator
     *
     * @expectedException \Symfony\Component\Validator\Exception\UnexpectedTypeException
     */
    public function testInvalidValueType()
    {
        $this->validator->validate(new \stdClass(), $this->constraint);
    }

    /**
     * @covers \CommerceGuys\Addressing\Validator\Constraints\AddressFormatConstraintValidator
     */
    public function testEmptyIsValid()
    {
        $this->validator->validate(new Address(), $this->constraint);
        $this->assertNoViolation();
    }

    /**
     * @covers \CommerceGuys\Addressing\Validator\Constraints\AddressFormatConstraintValidator
     */
    public function testAndorraOK()
    {
        $address = new Address();
        $address = $address
            ->withCountryCode('AD')
            ->withLocality("Parròquia d'Andorra la Vella")
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
    public function testAndorraNotOK()
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
    public function testUnitedStatesOK()
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
    public function testUnitedStatesNotOK()
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
    public function testUnitedStatesSubdivisionPostcodePattern()
    {
        // Test with subdivision-level postal code validation disabled.
        $this->constraint->extendedPostalCodeValidation = false;

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

        // Now test with the subdivision-level postal code validation enabled.
        $this->constraint->extendedPostalCodeValidation = true;
        $this->validator->validate($address, $this->constraint);
        $this->buildViolation($this->constraint->invalidMessage)
            ->atPath('[postalCode]')
            ->setInvalidValue('84025')
            ->assertRaised();
    }

    /**
     * @covers \CommerceGuys\Addressing\Validator\Constraints\AddressFormatConstraintValidator
     */
    public function testChinaOK()
    {
        $address = new Address();
        $address = $address
            ->withCountryCode('CN')
            ->withAdministrativeArea('Beijing Shi')
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
    public function testGermanAddress()
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

        // Testing with a empty city should fail.
        $address = $address->withLocality(null);

        $this->validator->validate($address, $this->constraint);
        $this->buildViolation($this->constraint->notBlankMessage)
            ->atPath('[locality]')
            ->setInvalidValue(null)
            ->assertRaised();
    }

    /**
     * @covers \CommerceGuys\Addressing\Validator\Constraints\AddressFormatConstraintValidator
     */
    public function testIrishAddress()
    {
        $address = new Address();
        $address = $address
            ->withCountryCode('IE')
            ->withAdministrativeArea('Co. Donegal')
            ->withLocality('Dublin')
            ->withAddressLine1('7424 118 Avenue NW')
            ->withGivenName('Conan')
            ->withFamilyName("O'Brien");

        $this->validator->validate($address, $this->constraint);
        $this->assertNoViolation();

        // Test the same address but leave the county empty. This address should be valid
        // since county is not required.
        $address = $address->withAdministrativeArea(null);

        $this->validator->validate($address, $this->constraint);
        $this->assertNoViolation();
    }

    /**
     * @covers \CommerceGuys\Addressing\Validator\Constraints\AddressFormatConstraintValidator
     */
    public function testChinaPostalCodeBadFormat()
    {
        $address = new Address();
        $address = $address
            ->withCountryCode('CN')
            ->withAdministrativeArea('Beijing Shi')
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
    public function testEmptyPostalCodeReportedAsGoodFormat()
    {
        $address = new Address();
        $address = $address
            ->withCountryCode('CL')
            ->withAdministrativeArea('Antofagasta')
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
    public function testChinaTaiwanOk()
    {
        $address = new Address();
        $address = $address
            ->withCountryCode('CN')
            ->withAdministrativeArea('Taiwan')
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
    public function testChinaTaiwanUnknownDistrict()
    {
        $address = new Address();
        $address = $address
            ->withCountryCode('CN')
            ->withAdministrativeArea('Taiwan')
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
    public function testStreetVerification()
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
     * @covers CommerceGuys\Addressing\Validator\Constraints\AddressFormatConstraintValidator
     */
    public function testJapan()
    {
        $address = new Address();
        $address = $address
            ->withCountryCode('JP')
            ->withAdministrativeArea('Kyoto')
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
    public function testCanadaMixedCasePostcode()
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
    public function testCanadaUnusedFields()
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
    public function testOverriddenRequiredFields()
    {
        // Confirm that it is possible to omit required name fields.
        // Intentionally uses the deprecated fields setting to confirm
        // that the BC layer works.
        $nameFields = [AddressField::GIVEN_NAME, AddressField::FAMILY_NAME];
        $this->constraint = new AddressFormatConstraint([
            'fields' => array_diff(AddressField::getAll(), $nameFields),
        ]);
        $address = new Address();
        $address = $address
            ->withCountryCode('CN')
            ->withAdministrativeArea('Beijing Shi')
            ->withLocality('Xicheng Qu')
            ->withPostalCode('123456')
            ->withAddressLine1('Yitiao Lu');
        $this->validator->validate($address, $this->constraint);
        $this->assertNoViolation();

        // Confirm that an optional override works the same way.
        $this->constraint->fields = [];
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
    public function testHiddenPostalCodeField()
    {
        // Confirm that postal code validation is skipped.
        $this->constraint->fieldOverrides = new FieldOverrides([
            AddressField::POSTAL_CODE => FieldOverride::HIDDEN,
        ]);
        $address = new Address();
        $address = $address
            ->withCountryCode('CN')
            ->withAdministrativeArea('Beijing Shi')
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
    public function testHiddenSubdivisionField()
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
}
