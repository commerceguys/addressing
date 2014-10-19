<?php

namespace CommerceGuys\Addressing\Tests\Validator;

use CommerceGuys\Addressing\Model\Address;
use CommerceGuys\Addressing\Metadata\AddressMetadataRepository;
use CommerceGuys\Addressing\Validator\Constraints\AddressFormat;
use CommerceGuys\Addressing\Validator\Constraints\AddressFormatValidator;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\LegacyValidator;

/**
 * @coversDefaultClass \CommerceGuys\Addressing\Validator\Constraints\AddressFormatValidator
 */
class AddressFormatValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Address;
     */
    protected $address;

    /**
     * @var AddressFormat
     */
    protected $constraint;

    /**
     * @var LegacyValidator
     */
    protected $validator;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->address = new Address();
        $this->constraint = new AddressFormat();
        $this->validator = Validation::createValidator();
    }

    /**
     * @covers CommerceGuys\Addressing\Validator\Constraints\AddressFormatValidator
     * @uses CommerceGuys\Addressing\Model\Address
     * @uses CommerceGuys\Addressing\Model\AddressFormat
     * @uses CommerceGuys\Addressing\Metadata\AddressMetadataRepository
     * @uses CommerceGuys\Addressing\Model\Subdivision
     */
    public function testUnitedStatesOK()
    {
        $this->address
          ->setCountryCode('US')
          ->setAdministrativeArea('US-CA')
          ->setLocality('Mountain View')
          ->setAddressLine1('1234 Somewhere')
          ->setPostalCode('94025');
        $this->assertNoViolations($this->address);
    }

    /**
     * @covers CommerceGuys\Addressing\Validator\Constraints\AddressFormatValidator
     * @uses CommerceGuys\Addressing\Model\Address
     * @uses CommerceGuys\Addressing\Model\AddressFormat
     * @uses CommerceGuys\Addressing\Metadata\AddressMetadataRepository
     * @uses CommerceGuys\Addressing\Model\Subdivision
     */
    public function testUnitedStatesNotOK()
    {
        $this->address
          ->setCountryCode("US")
          ->setAdministrativeArea("US-CA")
          ->setPostalCode("90961");
        $violations = $this->validator->validate($this->address, $this->constraint);

        $this->assertCount(2, $violations);
        $this->assertViolation('addressLine1', $this->constraint->notBlankMessage, $violations[0]);
        $this->assertViolation('locality', $this->constraint->notBlankMessage, $violations[1]);
    }

    /**
     * @covers CommerceGuys\Addressing\Validator\Constraints\AddressFormatValidator
     * @uses CommerceGuys\Addressing\Model\Address
     * @uses CommerceGuys\Addressing\Model\AddressFormat
     * @uses CommerceGuys\Addressing\Metadata\AddressMetadataRepository
     * @uses CommerceGuys\Addressing\Model\Subdivision
     */
    public function testChinaOK() {
        $this->address
            ->setCountryCode('CN')
            ->setAdministrativeArea('CN-11')
            ->setLocality('CN-11-30524e')
            ->setAddressLine1('Yitiao Lu')
            ->setPostalCode('123456');
        $this->assertNoViolations($this->address);
    }

    /**
     * @covers CommerceGuys\Addressing\Validator\Constraints\AddressFormatValidator
     * @uses CommerceGuys\Addressing\Model\Address
     * @uses CommerceGuys\Addressing\Model\AddressFormat
     * @uses CommerceGuys\Addressing\Metadata\AddressMetadataRepository
     * @uses CommerceGuys\Addressing\Model\Subdivision
     */
    public function testGermanAddress() {
        $this->address
            ->setCountryCode('DE')
            ->setLocality('Berlin')
            ->setAddressLine1('Huttenstr. 50')
            ->setPostalCode('10553')
            ->setOrganization('BMW AG Niederkassung Berlin')
            ->setRecipient('Herr Diefendorf');
        $this->assertNoViolations($this->address);

        // Testing with a empty city should fail.
        $this->address->setLocality(NULL);
        $violations = $this->validator->validate($this->address, $this->constraint);

        $this->assertCount(1, $violations);
        $this->assertViolation('locality', $this->constraint->notBlankMessage, $violations[0]);
    }

    /**
     * @covers CommerceGuys\Addressing\Validator\Constraints\AddressFormatValidator
     * @uses CommerceGuys\Addressing\Model\Address
     * @uses CommerceGuys\Addressing\Model\AddressFormat
     * @uses CommerceGuys\Addressing\Metadata\AddressMetadataRepository
     * @uses CommerceGuys\Addressing\Model\Subdivision
     */
    public function testIrishAddress() {
        $this->address
            ->setCountryCode('IE')
            ->setLocality('Dublin')
            ->setAdministrativeArea('IE-D')
            ->setAddressLine1('7424 118 Avenue NW')
            ->setRecipient("Conan O'Brien");
        $this->assertNoViolations($this->address);

        // Test the same address but leave the county empty. This address should be valid
        // since county is not required.
        $this->address->setAdministrativeArea(NULL);
        $violations = $this->validator->validate($this->address, $this->constraint);

        $this->assertCount(0, $violations);
    }

    /**
     * @covers CommerceGuys\Addressing\Validator\Constraints\AddressFormatValidator
     * @uses CommerceGuys\Addressing\Model\Address
     * @uses CommerceGuys\Addressing\Model\AddressFormat
     * @uses CommerceGuys\Addressing\Metadata\AddressMetadataRepository
     * @uses CommerceGuys\Addressing\Model\Subdivision
     */
    public function testChinaPostalCodeBadFormat() {
        $this->address
            ->setCountryCode('CN')
            ->setAdministrativeArea('CN-11')
            ->setLocality('CN-11-30524e')
            ->setPostalCode('1');
        $violations = $this->validator->validate($this->address, $this->constraint);

        $this->assertCount(2, $violations);
        $this->assertViolation('addressLine1', $this->constraint->notBlankMessage, $violations[0]);
        $this->assertViolation('postalCode', $this->constraint->invalidMessage, $violations[1]);
    }

    /**
     * @covers CommerceGuys\Addressing\Validator\Constraints\AddressFormatValidator
     * @uses CommerceGuys\Addressing\Model\Address
     * @uses CommerceGuys\Addressing\Model\AddressFormat
     * @uses CommerceGuys\Addressing\Metadata\AddressMetadataRepository
     * @uses CommerceGuys\Addressing\Model\Subdivision
     */
    public function testEmptyPostalCodeReportedAsGoodFormat() {
        $this->address
            ->setCountryCode('CL')
            ->setAddressLine1('GUSTAVO LE PAIGE ST #159')
            ->setAdministrativeArea('CL-AN')
            ->setLocality('CL-AN-2bb729')
            ->setPostalCode('');
        $this->assertNoViolations($this->address);

        // Now check for US addresses, which require a postal code. The following
        // address's postal code is wrong because it is missing a required field, not
        // because it doesn't match the expected postal code pattern.
        $this->address
          ->setCountryCode('US')
          ->setAdministrativeArea('US-CA')
          ->setLocality('California');
        $violations = $this->validator->validate($this->address, $this->constraint);

        $this->assertCount(1, $violations);
        $this->assertViolation('postalCode', $this->constraint->notBlankMessage, $violations[0]);
    }

    /**
     * @covers CommerceGuys\Addressing\Validator\Constraints\AddressFormatValidator
     * @uses CommerceGuys\Addressing\Model\Address
     * @uses CommerceGuys\Addressing\Model\AddressFormat
     * @uses CommerceGuys\Addressing\Metadata\AddressMetadataRepository
     * @uses CommerceGuys\Addressing\Model\Subdivision
     */
    public function testChinaTaiwanOk() {
        $this->address
            ->setCountryCode('CN')
            ->setAdministrativeArea('CN-71')
            ->setLocality('CN-71-dfbf10')
            ->setDependentLocality('CN-71-dfbf10-42fb60')
            ->setAddressLine1('12345 Yitiao Lu"')
            ->setPostalCode('407');

        $this->assertNoViolations($this->address);
    }

    /**
     * @covers CommerceGuys\Addressing\Validator\Constraints\AddressFormatValidator
     * @uses CommerceGuys\Addressing\Model\Address
     * @uses CommerceGuys\Addressing\Model\AddressFormat
     * @uses CommerceGuys\Addressing\Metadata\AddressMetadataRepository
     * @uses CommerceGuys\Addressing\Model\Subdivision
     */
    public function testChinaTaiwanUnknownDistrict() {
        $this->address
          ->setCountryCode('CN')
          ->setAdministrativeArea('CN-71')
          ->setLocality('CN-71-dfbf10')
          ->setDependentLocality('Foo Bar')
          ->setAddressLine1('12345 Yitiao Lu"')
          ->setPostalCode('407');
        $violations = $this->validator->validate($this->address, $this->constraint);

        $this->assertCount(1, $violations);
        $this->assertViolation('dependentLocality', $this->constraint->invalidMessage, $violations[0]);
    }

    /**
     * @covers CommerceGuys\Addressing\Validator\Constraints\AddressFormatValidator
     * @uses CommerceGuys\Addressing\Model\Address
     * @uses CommerceGuys\Addressing\Model\AddressFormat
     * @uses CommerceGuys\Addressing\Metadata\AddressMetadataRepository
     * @uses CommerceGuys\Addressing\Model\Subdivision
     */
    public function testStreetVerification() {
        $this->address
          ->setCountryCode('US')
          ->setAdministrativeArea('US-CA')
          ->setLocality('Mountain View')
          ->setPostalCode('94025');
        $violations = $this->validator->validate($this->address, $this->constraint);

        $this->assertCount(1, $violations);
        $this->assertNull($this->address->getAddressLine1());
        $this->assertNull($this->address->getAddressLine2());
        $this->assertViolation('addressLine1', $this->constraint->notBlankMessage, $violations[0]);
    }

    /**
     * @covers CommerceGuys\Addressing\Validator\Constraints\AddressFormatValidator
     * @uses CommerceGuys\Addressing\Model\Address
     * @uses CommerceGuys\Addressing\Model\AddressFormat
     * @uses CommerceGuys\Addressing\Metadata\AddressMetadataRepository
     * @uses CommerceGuys\Addressing\Model\Subdivision
     */
    public function testSubdivisionPostcodePattern() {
        // The correct postal code patters is used for a subdivision.
        $this->address
          ->setCountryCode('US')
          ->setAdministrativeArea('US-CA')
          ->setAddressLine1('1234 Somewhere')
          ->setLocality('Mountain View')
          ->setPostalCode('94025');
        $this->assertNoViolations($this->address);


        // When a invalid postal code is used for a subdivision it should fail.
        $this->address->setPostalCode('Foo Bar');
        $violations = $this->validator->validate($this->address, $this->constraint);

        $this->assertCount(1, $violations);
        $this->assertViolation('postalCode', $this->constraint->invalidMessage, $violations[0]);
    }

    /**
     * @covers CommerceGuys\Addressing\Validator\Constraints\AddressFormatValidator
     * @uses CommerceGuys\Addressing\Model\Address
     * @uses CommerceGuys\Addressing\Model\AddressFormat
     * @uses CommerceGuys\Addressing\Metadata\AddressMetadataRepository
     * @uses CommerceGuys\Addressing\Model\Subdivision
     */
    public function testJapan() {
        $this->address
            ->setCountryCode('JP')
            ->setAdministrativeArea('JP-26')
            ->setLocality('Shigeru Miyamoto')
            ->setAddressLine1('11-1 Kamitoba-hokotate-cho')
            ->setPostalCode('601-8501');

        $this->assertNoViolations($this->address);
    }

    /**
     * @covers CommerceGuys\Addressing\Validator\Constraints\AddressFormatValidator
     * @uses CommerceGuys\Addressing\Model\Address
     * @uses CommerceGuys\Addressing\Model\AddressFormat
     * @uses CommerceGuys\Addressing\Metadata\AddressMetadataRepository
     * @uses CommerceGuys\Addressing\Model\Subdivision
     */
    public function testCanadaMixedCasePostcode() {
        $this->address
            ->setCountryCode('CA')
            ->setRecipient('Joe Bloggs')
            ->setAddressLine1('11 East St')
            ->setLocality('Montreal')
            ->setAdministrativeArea('CA-QC')
            ->setPostalCode('H2b 2y5');

        $this->assertNoViolations($this->address);
    }

    /**
     * @covers CommerceGuys\Addressing\Validator\Constraints\AddressFormatValidator
     * @uses CommerceGuys\Addressing\Model\Address
     * @uses CommerceGuys\Addressing\Model\AddressFormat
     * @uses CommerceGuys\Addressing\Metadata\AddressMetadataRepository
     * @uses CommerceGuys\Addressing\Model\Subdivision
     */
    public function testCanadaUnusedFields() {
        $this->address
          ->setCountryCode('CA')
          ->setSortingCode('Foo Bar')
          ->setRecipient('Joe Bloggs')
          ->setAddressLine1('11 East St')
          ->setLocality('Montreal')
          ->setAdministrativeArea('CA-QC')
          ->setPostalCode('H2b 2y5');
        $violations = $this->validator->validate($this->address, $this->constraint);

        $this->assertViolation('sortingCode', $this->constraint->blankMessage, $violations[0]);
    }

    /**
     * @covers ::getRepository
     * @covers ::setRepository
     * @uses CommerceGuys\Addressing\Metadata\AddressMetadataRepository
     */
    public function testRepository() {
        $addressFormatValidator = new AddressFormatValidator();
        $this->assertInstanceOf('CommerceGuys\Addressing\Metadata\AddressMetadataRepository', $addressFormatValidator->getRepository());

        $addressMetadataRepository = new AddressMetadataRepository();
        $addressFormatValidator->setRepository($addressMetadataRepository);
        $this->assertEquals($addressMetadataRepository, $addressFormatValidator->getRepository());
    }

    /**
     * Helper function to assert an address that should be valid.
     *
     * @param \CommerceGuys\Addressing\Model\Address $address
     */
    protected function assertNoViolations(Address $address) {
        $violations = $this->validator->validate($address, $this->constraint);
        $this->assertCount(0, $violations);
    }

    /**
     * Helper function to assert an expected violation.
     *
     * @param string $fieldname
     * @param string $expectedMessage
     * @param \Symfony\Component\Validator\ConstraintViolation $violation
     */
    protected function assertViolation($fieldname, $expectedMessage, $violation) {
        $this->assertEquals('[' . $fieldname . ']', $violation->getPropertyPath());
        $this->assertEquals($expectedMessage, $violation->getMessage());
    }
}
