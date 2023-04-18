<?php

namespace CommerceGuys\Addressing\Tests\Subdivision;

use CommerceGuys\Addressing\Subdivision\Subdivision;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \CommerceGuys\Addressing\Subdivision\Subdivision
 */
final class SubdivisionTest extends TestCase
{
    /**
     * @covers ::__construct
     */
    public function testMissingProperty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $definition = [
            'country_code' => 'US',
        ];
        $subdivision = new Subdivision($definition);
    }

    /**
     * @covers ::__construct
     * @covers ::getParent
     * @covers ::getCountryCode
     * @covers ::getId
     * @covers ::getLocale
     * @covers ::getCode
     * @covers ::getLocalCode
     * @covers ::getName
     * @covers ::getLocalName
     * @covers ::getPostalCodePattern
     * @covers ::getChildren
     * @covers ::hasChildren
     */
    public function testValid(): void
    {
        $mockBuilder = $this->getMockBuilder(Subdivision::class);
        $mockBuilder = $mockBuilder->disableOriginalConstructor();
        $parent = $mockBuilder->getMock();
        $children = new ArrayCollection([$mockBuilder->getMock(), $mockBuilder->getMock()]);

        $definition = [
            'parent' => $parent,
            'country_code' => 'US',
            'id' => 'CA',
            'locale' => 'en',
            'code' => 'CA',
            'local_code' => 'CA!',
            'name' => 'California',
            'local_name' => 'California!',
            'postal_code_pattern' => '9[0-5]|96[01]',
            'children' => $children,
        ];
        $subdivision = new Subdivision($definition);

        $this->assertEquals($definition['parent'], $subdivision->getParent());
        $this->assertEquals($definition['country_code'], $subdivision->getCountryCode());
        $this->assertEquals($definition['id'], $subdivision->getId());
        $this->assertEquals($definition['locale'], $subdivision->getLocale());
        $this->assertEquals($definition['code'], $subdivision->getCode());
        $this->assertEquals($definition['local_code'], $subdivision->getLocalCode());
        $this->assertEquals($definition['name'], $subdivision->getName());
        $this->assertEquals($definition['local_name'], $subdivision->getLocalName());
        $this->assertEquals($definition['postal_code_pattern'], $subdivision->getPostalCodePattern());
        $this->assertEquals($definition['children'], $subdivision->getChildren());
        $this->assertTrue($subdivision->hasChildren());
    }
}
