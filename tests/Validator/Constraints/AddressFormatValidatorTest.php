<?php

namespace CommerceGuys\Addressing\Tests\Validator\Constraints;

use CommerceGuys\Addressing\Enum\AddressField;
use CommerceGuys\Addressing\Model\Address;
use CommerceGuys\Addressing\Validator\Constraints\AddressFormat as AddressFormatConstraint;
use CommerceGuys\Addressing\Validator\Constraints\AddressFormatValidator;
use Symfony\Component\Validator\Tests\Constraints\AbstractConstraintValidatorTest;

/**
 * @coversDefaultClass \CommerceGuys\Addressing\Validator\Constraints\AddressFormatValidator
 */
class AddressFormatValidatorTest extends AbstractConstraintValidatorTest
{
    /**
     * @var AddressFormatConstraint
     */
    protected $constraint;

    /**
     * {@inheritdoc}
     */
    public function setUp()
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
        return new AddressFormatValidator();
    }

    /**
     * @covers \CommerceGuys\Addressing\Validator\Constraints\AddressFormatValidator
     *
     * @uses \CommerceGuys\Addressing\Repository\AddressFormatRepository
     * @uses \CommerceGuys\Addressing\Repository\SubdivisionRepository
     * @expectedException \Symfony\Component\Validator\Exception\UnexpectedTypeException
     */
    public function testInvalidValueType()
    {
        $this->validator->validate(new \stdClass(), $this->constraint);
    }

    /**
     * @covers \CommerceGuys\Addressing\Validator\Constraints\AddressFormatValidator
     *
     * @uses \CommerceGuys\Addressing\Repository\AddressFormatRepository
     * @uses \CommerceGuys\Addressing\Repository\SubdivisionRepository
     * @uses \CommerceGuys\Addressing\Model\Address
     */
    public function testEmptyIsValid()
    {
        $this->validator->validate(new Address(), $this->constraint);
        $this->assertNoViolation();
    }

    /**
     * @covers \CommerceGuys\Addressing\Validator\Constraints\AddressFormatValidator
     *
     * @uses \CommerceGuys\Addressing\Repository\AddressFormatRepository
     * @uses \CommerceGuys\Addressing\Repository\SubdivisionRepository
     * @uses \CommerceGuys\Addressing\Repository\DefinitionTranslatorTrait
     * @uses \CommerceGuys\Addressing\Model\Address
     * @uses \CommerceGuys\Addressing\Model\AddressFormat
     * @uses \CommerceGuys\Addressing\Model\FormatStringTrait
     * @uses \CommerceGuys\Addressing\Model\Subdivision
     */
    public function testAndorraOK()
    {
        $address = new Address();
        $address = $address
            ->withCountryCode('AD')
            ->withLocality('AD-07')
            ->withPostalCode('AD500')
            ->withAddressLine1('C. Prat de la Creu, 62-64')
            ->withRecipient('Antoni Martí Petit');

        $this->validator->validate($address, $this->constraint);
        $this->assertNoViolation();
    }

    /**
     * @covers \CommerceGuys\Addressing\Validator\Constraints\AddressFormatValidator
     *
     * @uses \CommerceGuys\Addressing\Repository\AddressFormatRepository
     * @uses \CommerceGuys\Addressing\Repository\SubdivisionRepository
     * @uses \CommerceGuys\Addressing\Repository\DefinitionTranslatorTrait
     * @uses \CommerceGuys\Addressing\Model\Address
     * @uses \CommerceGuys\Addressing\Model\AddressFormat
     * @uses \CommerceGuys\Addressing\Model\FormatStringTrait
     * @uses \CommerceGuys\Addressing\Model\Subdivision
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
            ->withRecipient('Antoni Martí Petit');

        $this->validator->validate($address, $this->constraint);
        $this->buildViolation($this->constraint->invalidMessage)
            ->atPath('[locality]')
            ->setInvalidValue('INVALID')
            ->assertRaised();
    }

    /**
     * @covers \CommerceGuys\Addressing\Validator\Constraints\AddressFormatValidator
     *
     * @uses \CommerceGuys\Addressing\Repository\AddressFormatRepository
     * @uses \CommerceGuys\Addressing\Repository\SubdivisionRepository
     * @uses \CommerceGuys\Addressing\Repository\DefinitionTranslatorTrait
     * @uses \CommerceGuys\Addressing\Model\Address
     * @uses \CommerceGuys\Addressing\Model\AddressFormat
     * @uses \CommerceGuys\Addressing\Model\FormatStringTrait
     * @uses \CommerceGuys\Addressing\Model\Subdivision
     */
    public function testUnitedStatesOK()
    {
        $address = new Address();
        $address = $address
            ->withCountryCode('US')
            ->withAdministrativeArea('US-CA')
            ->withLocality('Mountain View')
            ->withPostalCode('94043')
            ->withAddressLine1('1098 Alta Ave')
            ->withRecipient('John Smith');

        $this->validator->validate($address, $this->constraint);
        $this->assertNoViolation();
    }

    /**
     * @covers \CommerceGuys\Addressing\Validator\Constraints\AddressFormatValidator
     *
     * @uses \CommerceGuys\Addressing\Repository\AddressFormatRepository
     * @uses \CommerceGuys\Addressing\Repository\SubdivisionRepository
     * @uses \CommerceGuys\Addressing\Repository\DefinitionTranslatorTrait
     * @uses \CommerceGuys\Addressing\Model\Address
     * @uses \CommerceGuys\Addressing\Model\AddressFormat
     * @uses \CommerceGuys\Addressing\Model\FormatStringTrait
     * @uses \CommerceGuys\Addressing\Model\Subdivision
     */
    public function testUnitedStatesNotOK()
    {
        $address = new Address();
        $address = $address
            ->withCountryCode('US')
            ->withAdministrativeArea('US-CA')
            // Fails the format-level check.
            ->withPostalCode('909')
            ->withRecipient('John Smith');

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
     * @covers \CommerceGuys\Addressing\Validator\Constraints\AddressFormatValidator
     *
     * @uses \CommerceGuys\Addressing\Repository\AddressFormatRepository
     * @uses \CommerceGuys\Addressing\Repository\SubdivisionRepository
     * @uses \CommerceGuys\Addressing\Repository\DefinitionTranslatorTrait
     * @uses \CommerceGuys\Addressing\Model\Address
     * @uses \CommerceGuys\Addressing\Model\AddressFormat
     * @uses \CommerceGuys\Addressing\Model\FormatStringTrait
     * @uses \CommerceGuys\Addressing\Model\Subdivision
     */
    public function testUnitedStatesSubdivisionPostcodePattern()
    {
        $address = new Address();
        $address = $address
            ->withCountryCode('US')
            ->withAdministrativeArea('US-CA')
            ->withLocality('Mountain View')
            ->withAddressLine1('1098 Alta Ave')
            // Satisfies the format-level check, fails the subdivision-level one.
            ->withPostalCode('84025')
            ->withRecipient('John Smith');

        $this->validator->validate($address, $this->constraint);
        $this->buildViolation($this->constraint->invalidMessage)
            ->atPath('[postalCode]')
            ->setInvalidValue('84025')
            ->assertRaised();
    }

    /**
     * @covers \CommerceGuys\Addressing\Validator\Constraints\AddressFormatValidator
     *
     * @uses \CommerceGuys\Addressing\Repository\AddressFormatRepository
     * @uses \CommerceGuys\Addressing\Repository\SubdivisionRepository
     * @uses \CommerceGuys\Addressing\Repository\DefinitionTranslatorTrait
     * @uses \CommerceGuys\Addressing\Model\Address
     * @uses \CommerceGuys\Addressing\Model\AddressFormat
     * @uses \CommerceGuys\Addressing\Model\FormatStringTrait
     * @uses \CommerceGuys\Addressing\Model\Subdivision
     * @uses \CommerceGuys\Addressing\Collection\LazySubdivisionCollection
     */
    public function testChinaOK()
    {
        $address = new Address();
        $address = $address
            ->withCountryCode('CN')
            ->withAdministrativeArea('CN-11')
            ->withLocality('CN-11-30524e')
            ->withPostalCode('123456')
            ->withAddressLine1('Yitiao Lu')
            ->withRecipient('John Smith');

        $this->validator->validate($address, $this->constraint);
        $this->assertNoViolation();
    }

    /**
     * @covers \CommerceGuys\Addressing\Validator\Constraints\AddressFormatValidator
     *
     * @uses \CommerceGuys\Addressing\Repository\AddressFormatRepository
     * @uses \CommerceGuys\Addressing\Repository\SubdivisionRepository
     * @uses \CommerceGuys\Addressing\Repository\DefinitionTranslatorTrait
     * @uses \CommerceGuys\Addressing\Model\Address
     * @uses \CommerceGuys\Addressing\Model\AddressFormat
     * @uses \CommerceGuys\Addressing\Model\FormatStringTrait
     * @uses \CommerceGuys\Addressing\Model\Subdivision
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
            ->withRecipient('Herr Diefendorf');

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
     * @covers \CommerceGuys\Addressing\Validator\Constraints\AddressFormatValidator
     *
     * @uses \CommerceGuys\Addressing\Repository\AddressFormatRepository
     * @uses \CommerceGuys\Addressing\Repository\SubdivisionRepository
     * @uses \CommerceGuys\Addressing\Repository\DefinitionTranslatorTrait
     * @uses \CommerceGuys\Addressing\Model\Address
     * @uses \CommerceGuys\Addressing\Model\AddressFormat
     * @uses \CommerceGuys\Addressing\Model\FormatStringTrait
     * @uses \CommerceGuys\Addressing\Model\Subdivision
     */
    public function testIrishAddress()
    {
        $address = new Address();
        $address = $address
            ->withCountryCode('IE')
            ->withAdministrativeArea('IE-D')
            ->withLocality('Dublin')
            ->withAddressLine1('7424 118 Avenue NW')
            ->withRecipient("Conan O'Brien");

        $this->validator->validate($address, $this->constraint);
        $this->assertNoViolation();

        // Test the same address but leave the county empty. This address should be valid
        // since county is not required.
        $address = $address->withAdministrativeArea(null);

        $this->validator->validate($address, $this->constraint);
        $this->assertNoViolation();
    }

    /**
     * @covers \CommerceGuys\Addressing\Validator\Constraints\AddressFormatValidator
     *
     * @uses \CommerceGuys\Addressing\Repository\AddressFormatRepository
     * @uses \CommerceGuys\Addressing\Repository\SubdivisionRepository
     * @uses \CommerceGuys\Addressing\Repository\DefinitionTranslatorTrait
     * @uses \CommerceGuys\Addressing\Model\Address
     * @uses \CommerceGuys\Addressing\Model\AddressFormat
     * @uses \CommerceGuys\Addressing\Model\FormatStringTrait
     * @uses \CommerceGuys\Addressing\Model\Subdivision
     * @uses \CommerceGuys\Addressing\Collection\LazySubdivisionCollection
     */
    public function testChinaPostalCodeBadFormat()
    {
        $address = new Address();
        $address = $address
            ->withCountryCode('CN')
            ->withAdministrativeArea('CN-11')
            ->withLocality('CN-11-30524e')
            ->withPostalCode('InvalidValue')
            ->withRecipient('John Smith');

        $this->validator->validate($address, $this->constraint);
        $this->buildViolation($this->constraint->notBlankMessage)
            ->atPath('[addressLine1]')
            ->setInvalidValue(null)
            ->buildNextViolation($this->constraint->invalidMessage)
            ->atPath('[postalCode]')
            ->assertRaised();
    }

    /**
     * @covers \CommerceGuys\Addressing\Validator\Constraints\AddressFormatValidator
     *
     * @uses \CommerceGuys\Addressing\Repository\AddressFormatRepository
     * @uses \CommerceGuys\Addressing\Repository\SubdivisionRepository
     * @uses \CommerceGuys\Addressing\Repository\DefinitionTranslatorTrait
     * @uses \CommerceGuys\Addressing\Model\Address
     * @uses \CommerceGuys\Addressing\Model\AddressFormat
     * @uses \CommerceGuys\Addressing\Model\FormatStringTrait
     * @uses \CommerceGuys\Addressing\Model\Subdivision
     * @uses \CommerceGuys\Addressing\Collection\LazySubdivisionCollection
     */
    public function testEmptyPostalCodeReportedAsGoodFormat()
    {
        $address = new Address();
        $address = $address
            ->withCountryCode('CL')
            ->withAdministrativeArea('CL-AN')
            ->withLocality('CL-AN-2bb729')
            ->withPostalCode('')
            ->withAddressLine1('GUSTAVO LE PAIGE ST #159')
            ->withRecipient('John Smith');

        $this->validator->validate($address, $this->constraint);
        $this->assertNoViolation();

        // Now check for US addresses, which require a postal code. The following
        // address's postal code is wrong because it is missing a required field, not
        // because it doesn't match the expected postal code pattern.
        $address = new Address();
        $address = $address
            ->withCountryCode('US')
            ->withAdministrativeArea('US-CA')
            ->withLocality('California')
            ->withAddressLine1('1098 Alta Ave')
            ->withRecipient('John Smith');

        $this->validator->validate($address, $this->constraint);
        $this->buildViolation($this->constraint->notBlankMessage)
            ->atPath('[postalCode]')
            ->setInvalidValue(null)
            ->assertRaised();
    }

    /**
     * @covers \CommerceGuys\Addressing\Validator\Constraints\AddressFormatValidator
     *
     * @uses \CommerceGuys\Addressing\Repository\AddressFormatRepository
     * @uses \CommerceGuys\Addressing\Repository\SubdivisionRepository
     * @uses \CommerceGuys\Addressing\Repository\DefinitionTranslatorTrait
     * @uses \CommerceGuys\Addressing\Model\Address
     * @uses \CommerceGuys\Addressing\Model\AddressFormat
     * @uses \CommerceGuys\Addressing\Model\FormatStringTrait
     * @uses \CommerceGuys\Addressing\Model\Subdivision
     * @uses \CommerceGuys\Addressing\Collection\LazySubdivisionCollection
     */
    public function testChinaTaiwanOk()
    {
        $address = new Address();
        $address = $address
            ->withCountryCode('CN')
            ->withAdministrativeArea('CN-71')
            ->withLocality('CN-71-dfbf10')
            ->withDependentLocality('CN-71-dfbf10-42fb60')
            ->withPostalCode('407')
            ->withAddressLine1('12345 Yitiao Lu')
            ->withRecipient('John Smith');

        $this->validator->validate($address, $this->constraint);
        $this->assertNoViolation();
    }

    /**
     * @covers \CommerceGuys\Addressing\Validator\Constraints\AddressFormatValidator
     *
     * @uses \CommerceGuys\Addressing\Repository\AddressFormatRepository
     * @uses \CommerceGuys\Addressing\Repository\SubdivisionRepository
     * @uses \CommerceGuys\Addressing\Repository\DefinitionTranslatorTrait
     * @uses \CommerceGuys\Addressing\Model\Address
     * @uses \CommerceGuys\Addressing\Model\AddressFormat
     * @uses \CommerceGuys\Addressing\Model\FormatStringTrait
     * @uses \CommerceGuys\Addressing\Model\Subdivision
     * @uses \CommerceGuys\Addressing\Collection\LazySubdivisionCollection
     */
    public function testChinaTaiwanUnknownDistrict()
    {
        $address = new Address();
        $address = $address
            ->withCountryCode('CN')
            ->withAdministrativeArea('CN-71')
            ->withLocality('CN-71-dfbf10')
            ->withDependentLocality('InvalidValue')
            ->withPostalCode('407')
            ->withAddressLine1('12345 Yitiao Lu')
            ->withRecipient('John Smith');

        $this->validator->validate($address, $this->constraint);
        $this->buildViolation($this->constraint->invalidMessage)
            ->atPath('[dependentLocality]')
            ->assertRaised();
    }

    /**
     * @covers \CommerceGuys\Addressing\Validator\Constraints\AddressFormatValidator
     *
     * @uses \CommerceGuys\Addressing\Repository\AddressFormatRepository
     * @uses \CommerceGuys\Addressing\Repository\SubdivisionRepository
     * @uses \CommerceGuys\Addressing\Repository\DefinitionTranslatorTrait
     * @uses \CommerceGuys\Addressing\Model\Address
     * @uses \CommerceGuys\Addressing\Model\AddressFormat
     * @uses \CommerceGuys\Addressing\Model\FormatStringTrait
     * @uses \CommerceGuys\Addressing\Model\Subdivision
     */
    public function testStreetVerification()
    {
        $address = new Address();
        $address = $address
            ->withCountryCode('US')
            ->withAdministrativeArea('US-CA')
            ->withLocality('Mountain View')
            ->withPostalCode('94043')
            ->withRecipient('John Smith');

        $this->validator->validate($address, $this->constraint);
        $this->buildViolation($this->constraint->notBlankMessage)
            ->atPath('[addressLine1]')
            ->setInvalidValue(null)
            ->assertRaised();
    }

    /**
     * @covers CommerceGuys\Addressing\Validator\Constraints\AddressFormatValidator
     *
     * @uses \CommerceGuys\Addressing\Repository\AddressFormatRepository
     * @uses \CommerceGuys\Addressing\Repository\SubdivisionRepository
     * @uses \CommerceGuys\Addressing\Repository\DefinitionTranslatorTrait
     * @uses \CommerceGuys\Addressing\Model\Address
     * @uses \CommerceGuys\Addressing\Model\AddressFormat
     * @uses \CommerceGuys\Addressing\Model\FormatStringTrait
     * @uses \CommerceGuys\Addressing\Model\Subdivision
     */
    public function testJapan()
    {
        $address = new Address();
        $address = $address
            ->withCountryCode('JP')
            ->withAdministrativeArea('JP-26')
            ->withLocality('Shigeru Miyamoto')
            ->withPostalCode('601-8501')
            ->withAddressLine1('11-1 Kamitoba-hokotate-cho')
            ->withRecipient('John Smith');

        $this->validator->validate($address, $this->constraint);
        $this->assertNoViolation();
    }

    /**
     * @covers \CommerceGuys\Addressing\Validator\Constraints\AddressFormatValidator
     *
     * @uses \CommerceGuys\Addressing\Repository\AddressFormatRepository
     * @uses \CommerceGuys\Addressing\Repository\SubdivisionRepository
     * @uses \CommerceGuys\Addressing\Repository\DefinitionTranslatorTrait
     * @uses \CommerceGuys\Addressing\Model\Address
     * @uses \CommerceGuys\Addressing\Model\AddressFormat
     * @uses \CommerceGuys\Addressing\Model\FormatStringTrait
     * @uses \CommerceGuys\Addressing\Model\Subdivision
     */
    public function testCanadaMixedCasePostcode()
    {
        $address = new Address();
        $address = $address
            ->withCountryCode('CA')
            ->withAdministrativeArea('CA-QC')
            ->withLocality('Montreal')
            ->withPostalCode('H2b 2y5')
            ->withAddressLine1('11 East St')
            ->withRecipient('Joe Bloggs');

        $this->validator->validate($address, $this->constraint);
        $this->assertNoViolation();
    }

    /**
     * @covers \CommerceGuys\Addressing\Validator\Constraints\AddressFormatValidator
     *
     * @uses \CommerceGuys\Addressing\Repository\AddressFormatRepository
     * @uses \CommerceGuys\Addressing\Repository\SubdivisionRepository
     * @uses \CommerceGuys\Addressing\Repository\DefinitionTranslatorTrait
     * @uses \CommerceGuys\Addressing\Model\Address
     * @uses \CommerceGuys\Addressing\Model\AddressFormat
     * @uses \CommerceGuys\Addressing\Model\FormatStringTrait
     * @uses \CommerceGuys\Addressing\Model\Subdivision
     */
    public function testCanadaUnusedFields()
    {
        $address = new Address();
        $address = $address
            ->withCountryCode('CA')
            ->withAdministrativeArea('CA-QC')
            ->withLocality('Montreal')
            ->withPostalCode('H2b 2y5')
            ->withSortingCode('InvalidValue')
            ->withAddressLine1('11 East St')
            ->withRecipient('Joe Bloggs');

        $this->validator->validate($address, $this->constraint);
        $this->buildViolation($this->constraint->blankMessage)
            ->atPath('[sortingCode]')
            ->assertRaised();
    }

    /**
     * @covers \CommerceGuys\Addressing\Validator\Constraints\AddressFormatValidator
     *
     * @uses \CommerceGuys\Addressing\Repository\AddressFormatRepository
     * @uses \CommerceGuys\Addressing\Repository\SubdivisionRepository
     * @uses \CommerceGuys\Addressing\Repository\DefinitionTranslatorTrait
     * @uses \CommerceGuys\Addressing\Model\Address
     * @uses \CommerceGuys\Addressing\Model\AddressFormat
     * @uses \CommerceGuys\Addressing\Model\FormatStringTrait
     * @uses \CommerceGuys\Addressing\Model\Subdivision
     * @uses \CommerceGuys\Addressing\Collection\LazySubdivisionCollection
     */
    public function testConstraintFields()
    {
        $allFields = AddressField::getAll();

        $this->constraint->fields = array_diff($allFields, [AddressField::RECIPIENT]);
        $address = new Address();
        $address = $address
            ->withCountryCode('CN')
            ->withAdministrativeArea('CN-11')
            ->withLocality('CN-11-30524e')
            ->withPostalCode('123456')
            ->withAddressLine1('Yitiao Lu');
        $this->validator->validate($address, $this->constraint);
        $this->assertNoViolation();

        $this->constraint->fields = array_diff($allFields, [AddressField::POSTAL_CODE]);
        $address = $address
            ->withPostalCode('INVALID')
            ->withRecipient('John Smith');
        $this->validator->validate($address, $this->constraint);
        $this->assertNoViolation();

        $this->constraint->fields = array_diff($allFields, [AddressField::ADMINISTRATIVE_AREA]);
        $address = $address
            ->withAdministrativeArea('INVALID')
            ->withPostalCode('123456');
        $this->validator->validate($address, $this->constraint);
        $this->assertNoViolation();

        $address = $address
            ->withAdministrativeArea('CN-11')
            ->withLocality('INVALID');
        $this->validator->validate($address, $this->constraint);
        $this->assertNoViolation();
    }
}
