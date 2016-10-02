<?php

namespace CommerceGuys\Addressing\Tests\AddressFormat;

use CommerceGuys\Addressing\AddressFormat\AddressField;
use CommerceGuys\Addressing\AddressFormat\AddressFormat;
use CommerceGuys\Addressing\AddressFormat\AdministrativeAreaType;
use CommerceGuys\Addressing\AddressFormat\DependentLocalityType;
use CommerceGuys\Addressing\AddressFormat\LocalityType;
use CommerceGuys\Addressing\AddressFormat\PostalCodeType;

/**
 * @coversDefaultClass \CommerceGuys\Addressing\AddressFormat\AddressFormat
 */
class AddressFormatTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     *
     * @expectedException \InvalidArgumentException
     */
    public function testMissingProperty()
    {
        $definition = [
            'country_code' => 'US',
        ];
        $addressFormat = new AddressFormat($definition);
    }

    /**
     * @covers ::__construct
     *
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidSubdivision()
    {
        $definition = [
            'country_code' => 'US',
            'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%dependentLocality",
            'required_fields' => [AddressField::ADDRESS_LINE1],
            'dependent_locality_type' => 'WRONG',
        ];
        $addressFormat = new AddressFormat($definition);
    }

    /**
     * @covers ::__construct
     * @covers ::getCountryCode
     * @covers ::getLocale
     * @covers ::getFormat
     * @covers ::getLocalFormat
     * @covers ::getUsedFields
     * @covers ::getUsedSubdivisionFields
     * @covers ::getRequiredFields
     * @covers ::getUppercaseFields
     * @covers ::getAdministrativeAreaType
     * @covers ::getLocalityType
     * @covers ::getDependentLocalityType
     * @covers ::getPostalCodeType
     * @covers ::getPostalCodePattern
     * @covers ::getPostalCodePrefix
     * @covers ::getSubdivisionDepth
     */
    public function testValid()
    {
        $definition = [
            'country_code' => 'US',
            'locale' => 'en',
            'format' => "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality, %administrativeArea %postalCode",
            // The local format is made up, US doesn't have one usually.
            'local_format' => '%postalCode\n%addressLine1\n%organization\n%givenName %familyName',
            'required_fields' => [
                AddressField::ADMINISTRATIVE_AREA,
                AddressField::LOCALITY,
                AddressField::POSTAL_CODE,
                AddressField::ADDRESS_LINE1,
            ],
            'uppercase_fields' => [
                AddressField::ADMINISTRATIVE_AREA,
                AddressField::LOCALITY,
            ],
            'administrative_area_type' => AdministrativeAreaType::STATE,
            'locality_type' => LocalityType::CITY,
            'dependent_locality_type' => DependentLocalityType::DISTRICT,
            'postal_code_type' => PostalCodeType::ZIP,
            'postal_code_pattern' => '(\d{5})(?:[ \-](\d{4}))?',
            // US doesn't use postal code prefixes, fake one for test purposes.
            'postal_code_prefix' => 'US',
            'subdivision_depth' => 1,
        ];
        $addressFormat = new AddressFormat($definition);

        $this->assertEquals($definition['country_code'], $addressFormat->getCountryCode());
        $this->assertEquals($definition['locale'], $addressFormat->getLocale());
        $this->assertEquals($definition['format'], $addressFormat->getFormat());
        $this->assertEquals($definition['local_format'], $addressFormat->getLocalFormat());
        $this->assertEquals($definition['required_fields'], $addressFormat->getRequiredFields());
        $this->assertEquals($definition['uppercase_fields'], $addressFormat->getUppercaseFields());
        $this->assertEquals($definition['administrative_area_type'], $addressFormat->getAdministrativeAreaType());
        $this->assertEquals($definition['locality_type'], $addressFormat->getLocalityType());
        // The format has no %dependentLocality, the type must be NULL.
        $this->assertNull($addressFormat->getDependentLocalityType());
        $this->assertEquals($definition['postal_code_type'], $addressFormat->getPostalCodeType());
        $this->assertEquals($definition['postal_code_pattern'], $addressFormat->getPostalCodePattern());
        $this->assertEquals($definition['postal_code_prefix'], $addressFormat->getPostalCodePrefix());
        $this->assertEquals($definition['subdivision_depth'], $addressFormat->getSubdivisionDepth());

        $expectedUsedFields = [
            AddressField::ADMINISTRATIVE_AREA,
            AddressField::LOCALITY,
            AddressField::POSTAL_CODE,
            AddressField::ADDRESS_LINE1,
            AddressField::ADDRESS_LINE2,
            AddressField::ORGANIZATION,
            AddressField::GIVEN_NAME,
            AddressField::FAMILY_NAME,
        ];
        $this->assertEquals($expectedUsedFields, $addressFormat->getUsedFields());
        $expectedUsedSubdivisionFields = [
            AddressField::ADMINISTRATIVE_AREA,
            AddressField::LOCALITY,
        ];
        $this->assertEquals($expectedUsedSubdivisionFields, $addressFormat->getUsedSubdivisionFields());
    }
}
