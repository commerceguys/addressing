<?php

namespace CommerceGuys\Addressing\Tests\Model;

use CommerceGuys\Addressing\Enum\AddressField;
use CommerceGuys\Addressing\Enum\AdministrativeAreaType;
use CommerceGuys\Addressing\Enum\DependentLocalityType;
use CommerceGuys\Addressing\Enum\LocalityType;
use CommerceGuys\Addressing\Enum\PostalCodeType;
use CommerceGuys\Addressing\Model\AddressFormat;

/**
 * @coversDefaultClass \CommerceGuys\Addressing\Model\AddressFormat
 */
class AddressFormatTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AddressFormat
     */
    protected $addressFormat;

    public function setUp()
    {
        $this->addressFormat = new AddressFormat();
    }

    /**
     * @covers ::getCountryCode
     * @covers ::setCountryCode
     */
    public function testCountryCode()
    {
        $this->addressFormat->setCountryCode('US');
        $this->assertEquals('US', $this->addressFormat->getCountryCode());
    }

    /**
     * @covers ::getFormat
     * @covers ::setFormat
     * @covers ::getUsedFields
     * @covers ::getUsedSubdivisionFields
     * @covers ::getGroupedFields
     */
    public function testFormat()
    {
        $format = "%recipient\n%organization\n%addressLine1\n%addressLine2\n%locality, %postalCode";
        $this->addressFormat->setFormat($format);
        $this->assertEquals($format, $this->addressFormat->getFormat());

        $expectedUsedFields = [
            AddressField::LOCALITY,
            AddressField::POSTAL_CODE,
            AddressField::ADDRESS_LINE1,
            AddressField::ADDRESS_LINE2,
            AddressField::ORGANIZATION,
            AddressField::RECIPIENT,
        ];
        $this->assertEquals($expectedUsedFields, $this->addressFormat->getUsedFields());

        $expectedUsedSubdivisionFields = [
            AddressField::LOCALITY,
        ];
        $this->assertEquals($expectedUsedSubdivisionFields, $this->addressFormat->getUsedSubdivisionFields());

        $expectedGroupedFields = [
            [AddressField::RECIPIENT],
            [AddressField::ORGANIZATION],
            [AddressField::ADDRESS_LINE1],
            [AddressField::ADDRESS_LINE2],
            [AddressField::LOCALITY, AddressField::POSTAL_CODE],
        ];
        $this->assertEquals($expectedGroupedFields, $this->addressFormat->getGroupedFields());
    }

    /**
     * @covers ::getRequiredFields
     * @covers ::setRequiredFields
     */
    public function testRequiredFields()
    {
        $requiredFields = [
            AddressField::ADMINISTRATIVE_AREA,
            AddressField::LOCALITY,
            AddressField::POSTAL_CODE,
            AddressField::ADDRESS_LINE1,
        ];
        $this->addressFormat->setRequiredFields($requiredFields);
        $this->assertEquals($requiredFields, $this->addressFormat->getRequiredFields());
    }

    /**
     * @covers ::getUppercaseFields
     * @covers ::setUppercaseFields
     */
    public function testUppercaseFields()
    {
        $uppercaseFields = [
            AddressField::ADMINISTRATIVE_AREA,
            AddressField::LOCALITY,
        ];
        $this->addressFormat->setUppercaseFields($uppercaseFields);
        $this->assertEquals($uppercaseFields, $this->addressFormat->getUppercaseFields());
    }

    /**
     * @covers ::getAdministrativeAreaType
     * @covers ::setAdministrativeAreaType
     */
    public function testAdministrativeAreaType()
    {
        $areaType = AdministrativeAreaType::STATE;
        $this->addressFormat->setAdministrativeAreaType($areaType);
        $this->assertEquals($areaType, $this->addressFormat->getAdministrativeAreaType());
    }

    /**
     * @covers ::getLocalityType
     * @covers ::setLocalityType
     */
    public function testLocalityType()
    {
        $localityType = LocalityType::CITY;
        $this->addressFormat->setLocalityType($localityType);
        $this->assertEquals($localityType, $this->addressFormat->getLocalityType());
    }

    /**
     * @covers ::getDependentLocalityType
     * @covers ::setDependentLocalityType
     */
    public function testDependentLocalityType()
    {
        $dependentLocalityType = DependentLocalityType::DISTRICT;
        $this->addressFormat->setDependentLocalityType($dependentLocalityType);
        $this->assertEquals($dependentLocalityType, $this->addressFormat->getDependentLocalityType());
    }

    /**
     * @covers ::getPostalCodeType
     * @covers ::setPostalCodeType
     */
    public function testPostalCodeType()
    {
        $postalCodeType = PostalCodeType::ZIP;
        $this->addressFormat->setPostalCodeType($postalCodeType);
        $this->assertEquals($postalCodeType, $this->addressFormat->getPostalCodeType());
    }

    /**
     * @covers ::getPostalCodePattern
     * @covers ::setPostalCodePattern
     */
    public function testPostalCodePattern()
    {
        $this->addressFormat->setPostalCodePattern('(\d{5})(?:[ \-](\d{4}))?');
        $this->assertEquals('(\d{5})(?:[ \-](\d{4}))?', $this->addressFormat->getPostalCodePattern());
    }

    /**
     * @covers ::getPostalCodePrefix
     * @covers ::setPostalCodePrefix
     */
    public function testPostalCodePrefix()
    {
        // US doesn't use postal code prefixes, so there's no good example here.
        $this->addressFormat->setPostalCodePrefix('CA');
        $this->assertEquals('CA', $this->addressFormat->getPostalCodePrefix());
    }

    /**
     * @covers ::getLocale
     * @covers ::setLocale
     */
    public function testLocale()
    {
        $this->addressFormat->setLocale('en');
        $this->assertEquals('en', $this->addressFormat->getLocale());
    }
}
