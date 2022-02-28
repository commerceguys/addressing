<?php

namespace CommerceGuys\Addressing\Tests\Zone;

use CommerceGuys\Addressing\Address;
use CommerceGuys\Addressing\Zone\Zone;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \CommerceGuys\Addressing\Zone\Zone
 */
final class ZoneTest extends TestCase
{
    /**
     * @covers ::__construct
     *
     *
     */
    public function testMissingProperty()
    {
        $this->expectException(\InvalidArgumentException::class);
        $definition = [
            'id' => 'test',
        ];
        $zone = new Zone($definition);
    }

    /**
     * @covers ::__construct
     *
     *
     */
    public function testInvalidTerritories()
    {
        $this->expectException(\InvalidArgumentException::class);
        $definition = [
            'id' => 'test',
            'label' => 'Test',
            'territories' => 'WRONG',
        ];
        $zone = new Zone($definition);
    }

    /**
     * @covers ::__construct
     * @covers ::getId
     * @covers ::getLabel
     * @covers ::getTerritories
     * @covers ::match
     */
    public function testValid()
    {
        $definition = [
            'id' => 'de_fr',
            'label' => 'Germany and France',
            'territories' => [
                ['country_code' => 'DE'],
                ['country_code' => 'FR'],
            ],
        ];
        $zone = new Zone($definition);

        $this->assertEquals($definition['id'], $zone->getId());
        $this->assertEquals($definition['label'], $zone->getLabel());
        $this->assertCount(2, $zone->getTerritories());
        $this->assertEquals($definition['territories'][0]['country_code'], $zone->getTerritories()[0]->getCountryCode());
        $this->assertEquals($definition['territories'][1]['country_code'], $zone->getTerritories()[1]->getCountryCode());

        $german_address = new Address('DE');
        $serbian_address = new Address('RS');
        $this->assertTrue($zone->match($german_address));
        $this->assertFalse($zone->match($serbian_address));
    }
}
