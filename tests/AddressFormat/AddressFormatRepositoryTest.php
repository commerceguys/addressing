<?php

namespace CommerceGuys\Addressing\Tests\AddressFormat;

use CommerceGuys\Addressing\AddressFormat\AddressFormat;
use CommerceGuys\Addressing\AddressFormat\AddressFormatRepository;
use CommerceGuys\Addressing\AddressFormat\AdministrativeAreaType;
use CommerceGuys\Addressing\AddressFormat\LocalityType;
use CommerceGuys\Addressing\AddressFormat\PostalCodeType;

/**
 * @coversDefaultClass \CommerceGuys\Addressing\AddressFormat\AddressFormatRepository
 */
class AddressFormatRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::get
     * @covers ::processDefinition
     * @covers ::getGenericDefinition
     * @covers ::getDefinitions
     */
    public function testGet()
    {
        $addressFormatRepository = new AddressFormatRepository();
        $addressFormat = $addressFormatRepository->get('ES');
        // Confirm that the right class has been returned, a known value has
        // been successfully populated, and defaults have been merged.
        $this->assertInstanceOf(AddressFormat::class, $addressFormat);
        $this->assertEquals('ES', $addressFormat->getCountryCode());
        $this->assertEquals(AdministrativeAreaType::PROVINCE, $addressFormat->getAdministrativeAreaType());
        $this->assertEquals(LocalityType::CITY, $addressFormat->getLocalityType());
        $this->assertEquals(PostalCodeType::POSTAL, $addressFormat->getPostalCodeType());
        $this->assertEquals('\\d{5}', $addressFormat->getPostalCodePattern());

        // Confirm that passing a lowercase country code works.
        $anotherAddressFormat = $addressFormatRepository->get('es');
        $this->assertSame($anotherAddressFormat, $addressFormat);
    }

    /**
     * @covers ::get
     * @covers ::processDefinition
     * @covers ::getGenericDefinition
     * @covers ::getDefinitions
     */
    public function testGetNonExistingAddressFormat()
    {
        $addressFormatRepository = new AddressFormatRepository();
        $addressFormat = $addressFormatRepository->get('ZZ');
        $this->assertEquals('ZZ', $addressFormat->getCountryCode());
    }

    /**
     * @covers ::getAll
     * @covers ::processDefinition
     * @covers ::getGenericDefinition
     * @covers ::getDefinitions
     */
    public function testGetAll()
    {
        $addressFormatRepository = new AddressFormatRepository();
        $addressFormats = $addressFormatRepository->getAll();
        $this->assertArrayHasKey('ES', $addressFormats);
        $this->assertArrayHasKey('RS', $addressFormats);
        $this->assertEquals('ES', $addressFormats['ES']->getCountryCode());
        $this->assertEquals(LocalityType::CITY, $addressFormats['ES']->getLocalityType());
        $this->assertEquals('RS', $addressFormats['RS']->getCountryCode());
        $this->assertEquals(LocalityType::CITY, $addressFormats['RS']->getLocalityType());
    }
}
