<?php

namespace CommerceGuys\Addressing\Tests\Zone;

use CommerceGuys\Addressing\Address;
use CommerceGuys\Addressing\Zone\ZoneTerritory;

/**
 * @coversDefaultClass \CommerceGuys\Addressing\Zone\ZoneTerritory
 */
class ZoneTerritoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     *
     * @expectedException \InvalidArgumentException
     */
    public function testMissingProperty()
    {
        $territory = new ZoneTerritory([]);
    }

    /**
     * @covers ::__construct
     * @covers ::getCountryCode
     * @covers ::getAdministrativeArea
     * @covers ::getLocality
     * @covers ::getDependentLocality
     * @covers ::getIncludedPostalCodes
     * @covers ::getExcludedPostalCodes
     * @covers ::match
     */
    public function testValid()
    {
        $definition = [
            'country_code' => 'BR',
            'administrative_area' => 'RJ',
            'locality' => 'Areal',
            'dependent_locality' => 'Random',
            'included_postal_codes' => '123456',
            'excluded_postal_codes' => '789',
        ];
        $territory = new ZoneTerritory($definition);

        $this->assertEquals($definition['country_code'], $territory->getCountryCode());
        $this->assertEquals($definition['administrative_area'], $territory->getAdministrativeArea());
        $this->assertEquals($definition['locality'], $territory->getLocality());
        $this->assertEquals($definition['dependent_locality'], $territory->getDependentLocality());
        $this->assertEquals($definition['included_postal_codes'], $territory->getIncludedPostalCodes());
        $this->assertEquals($definition['excluded_postal_codes'], $territory->getExcludedPostalCodes());

        $brazilian_address = new Address('BR', 'RJ', 'Areal', 'Random', '123456');
        $serbian_address = new Address('RS');
        $this->assertTrue($territory->match($brazilian_address));
        $this->assertFalse($territory->match($serbian_address));
    }
}
