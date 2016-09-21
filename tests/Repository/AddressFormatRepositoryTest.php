<?php

namespace CommerceGuys\Addressing\Tests\Repository;

use CommerceGuys\Addressing\Enum\AdministrativeAreaType;
use CommerceGuys\Addressing\Enum\LocalityType;
use CommerceGuys\Addressing\Enum\PostalCodeType;
use CommerceGuys\Addressing\Repository\AddressFormatRepository;

/**
 * @coversDefaultClass \CommerceGuys\Addressing\Repository\AddressFormatRepository
 */
class AddressFormatRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::get
     * @covers ::getDefinitions
     */
    public function testGet()
    {
        $addressFormatRepository = new AddressFormatRepository();
        $addressFormat = $addressFormatRepository->get('ES');
        // Confirm that the right class has been returned, a known value has
        // been successfully populated, and defaults have been merged.
        $this->assertInstanceOf('CommerceGuys\Addressing\Model\AddressFormat', $addressFormat);
        $this->assertEquals('ES', $addressFormat->getCountryCode());
        $this->assertEquals(AdministrativeAreaType::PROVINCE, $addressFormat->getAdministrativeAreaType());
        $this->assertEquals(LocalityType::CITY, $addressFormat->getLocalityType());
        $this->assertEquals(PostalCodeType::POSTAL, $addressFormat->getPostalCodeType());
        $this->assertEquals('\\d{5}', $addressFormat->getPostalCodePattern());
    }

    /**
     * @covers ::get
     * @covers ::getDefinitions
     */
    public function testGetNonExistingAddressFormat()
    {
        $addressFormatRepository = new AddressFormatRepository();
        $addressFormat = $addressFormatRepository->get('Kitten');
        $this->assertEquals('ZZ', $addressFormat->getCountryCode());
    }

    /**
     * @covers ::getAll
     * @covers ::getDefinitions
     */
    public function testGetAll()
    {
        $addressFormatRepository = new AddressFormatRepository();
        $addressFormats = $addressFormatRepository->getAll();
        $this->assertArrayHasKey('ES', $addressFormats);
        $this->assertArrayHasKey('ZZ', $addressFormats);
        $this->assertEquals('ES', $addressFormats['ES']->getCountryCode());
        $this->assertEquals(LocalityType::CITY, $addressFormats['ES']->getLocalityType());
        $this->assertEquals('ZZ', $addressFormats['ZZ']->getCountryCode());
        $this->assertEquals(LocalityType::CITY, $addressFormats['ZZ']->getLocalityType());
    }
}
