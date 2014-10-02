<?php

namespace CommerceGuys\Addressing\Tests\Metadata;

use CommerceGuys\Addressing\Metadata\Subdivision;

/**
 * @coversDefaultClass \CommerceGuys\Addressing\Metadata\Subdivision
 */
class SubdivisionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Subdivision
     */
    protected $subdivision;

    public function setUp()
    {
        $this->subdivision = new Subdivision();
    }

    /**
     * @covers ::getParent
     * @covers ::setParent
     * @covers ::getChildren
     * @covers ::setChildren
     * @covers ::hasChildren
     * @uses CommerceGuys\Addressing\Metadata\Subdivision::setId
     * @uses CommerceGuys\Addressing\Metadata\Subdivision::setCountryCode
     * @uses CommerceGuys\Addressing\Metadata\Subdivision::getCode
     * @uses CommerceGuys\Addressing\Metadata\Subdivision::setCode
     */
    public function testHierarchy()
    {
        // There's no real example here because the US only has one level of
        // subdivisions. So, we'll add Texas as California's parent AND child.
        $texas = new Subdivision();
        $texas->setCountryCode('US');
        $texas->setId('US-TX');
        $texas->setCode('TX');

        $this->subdivision->setParent($texas);
        $this->assertEquals($this->subdivision->getParent(), $texas);

        $this->assertEquals($this->subdivision->hasChildren(), false);
        $this->subdivision->setChildren(array($texas));
        $this->assertEquals($this->subdivision->hasChildren(), true);
        $this->assertEquals($this->subdivision->getChildren(), array($texas));
    }

    /**
     * @covers ::getCountryCode
     * @covers ::setCountryCode
     */
    public function testCountryCode()
    {
        $this->subdivision->setCountryCode('US');
        $this->assertEquals($this->subdivision->getCountryCode(), 'US');
    }

    /**
     * @covers ::getId
     * @covers ::setId
     */
    public function testId()
    {
        $this->subdivision->setId('US-CA');
        $this->assertEquals($this->subdivision->getId(), 'US-CA');
    }

    /**
     * @covers ::getCode
     * @covers ::setCode
     */
    public function testCode()
    {
        $this->subdivision->setCode('CA');
        $this->assertEquals($this->subdivision->getCode(), 'CA');
    }

    /**
     * @covers ::getName
     * @covers ::setName
     */
    public function testName()
    {
        $this->subdivision->setName('California');
        $this->assertEquals($this->subdivision->getName(), 'California');
    }

    /**
     * @covers ::getPostalCodePattern
     * @covers ::setPostalCodePattern
     */
    public function testPostalCodePattern()
    {
        $this->subdivision->setPostalCodePattern('9[0-5]|96[01]');
        $this->assertEquals($this->subdivision->getPostalCodePattern(), '9[0-5]|96[01]');
    }

    /**
     * @covers ::getLocale
     * @covers ::setLocale
     */
    public function testLocale()
    {
        $this->subdivision->setLocale('en');
        $this->assertEquals($this->subdivision->getLocale(), 'en');
    }
}
