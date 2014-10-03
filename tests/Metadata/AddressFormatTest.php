<?php

namespace CommerceGuys\Addressing\Tests\Metadata;

use CommerceGuys\Addressing\Metadata\AddressFormat;

/**
 * @coversDefaultClass \CommerceGuys\Addressing\Metadata\AddressFormat
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
        $this->assertEquals($this->addressFormat->getCountryCode(), 'US');
    }

    /**
     * @covers ::getFormat
     * @covers ::setFormat
     */
    public function testFormat()
    {
        $format = "%recipient\n%organization\n%address\n%locality, %administrative_area %postal_code";
        $this->addressFormat->setFormat($format);
        $this->assertEquals($this->addressFormat->getFormat(), $format);
    }

    /**
     * @covers ::getRequiredFields
     * @covers ::setRequiredFields
     */
    public function testRequiredFields()
    {
        $requiredFields = array(
            AddressFormat::FIELD_ADMINISTRATIVE_AREA,
            AddressFormat::FIELD_LOCALITY,
            AddressFormat::FIELD_POSTAL_CODE,
            AddressFormat::FIELD_ADDRESS,
        );
        $this->addressFormat->setRequiredFields($requiredFields);
        $this->assertEquals($this->addressFormat->getRequiredFields(), $requiredFields);
    }

    /**
     * @covers ::getUppercaseFields
     * @covers ::setUppercaseFields
     */
    public function testUppercaseFields()
    {
        $uppercaseFields = array(
            AddressFormat::FIELD_ADMINISTRATIVE_AREA,
            AddressFormat::FIELD_LOCALITY,
        );
        $this->addressFormat->setUppercaseFields($uppercaseFields);
        $this->assertEquals($this->addressFormat->getUppercaseFields(), $uppercaseFields);
    }

    /**
     * @covers ::getAdministrativeAreaType
     * @covers ::setAdministrativeAreaType
     */
    public function testAdministrativeAreaType()
    {
        $areaType = AddressFormat::ADMINISTRATIVE_AREA_TYPE_STATE;
        $this->addressFormat->setAdministrativeAreaType($areaType);
        $this->assertEquals($this->addressFormat->getAdministrativeAreaType(), $areaType);
    }

    /**
     * @covers ::getPostalCodeType
     * @covers ::setPostalCodeType
     */
    public function testPostalCodeType()
    {
        $postalCodeType = AddressFormat::POSTAL_CODE_TYPE_ZIP;
        $this->addressFormat->setPostalCodeType($postalCodeType);
        $this->assertEquals($this->addressFormat->getPostalCodeType(), $postalCodeType);
    }

    /**
     * @covers ::getPostalCodePattern
     * @covers ::setPostalCodePattern
     */
    public function testPostalCodePattern()
    {
        $this->addressFormat->setPostalCodePattern('(\d{5})(?:[ \-](\d{4}))?');
        $this->assertEquals($this->addressFormat->getPostalCodePattern(), '(\d{5})(?:[ \-](\d{4}))?');
    }

    /**
     * @covers ::getPostalCodePrefix
     * @covers ::setPostalCodePrefix
     */
    public function testPostalCodePrefix()
    {
        // US doesn't use postal code prefixes, so there's no good example here.
        $this->addressFormat->setPostalCodePrefix('CA');
        $this->assertEquals($this->addressFormat->getPostalCodePrefix(), 'CA');
    }

    /**
     * @covers ::getLocale
     * @covers ::setLocale
     */
    public function testLocale()
    {
        $this->addressFormat->setLocale('en');
        $this->assertEquals($this->addressFormat->getLocale(), 'en');
    }
}
