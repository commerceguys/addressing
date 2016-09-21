<?php

namespace CommerceGuys\Addressing\Tests\Model;

use CommerceGuys\Addressing\Enum\PatternType;
use CommerceGuys\Addressing\Model\Subdivision;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @coversDefaultClass \CommerceGuys\Addressing\Model\Subdivision
 */
class SubdivisionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Subdivision
     */
    protected $subdivision;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->subdivision = new Subdivision();
    }

    /**
     * @covers ::getParent
     * @covers ::setParent
     */
    public function testParent()
    {
        $parent = $this
            ->getMockBuilder('CommerceGuys\Addressing\Model\Subdivision')
            ->getMock();
        $this->subdivision->setParent($parent);
        $this->assertEquals($parent, $this->subdivision->getParent());
    }

    /**
     * @covers ::__construct
     * @covers ::getChildren
     * @covers ::setChildren
     * @covers ::hasChildren
     */
    public function testChildren()
    {
        $firstChild = $this
            ->getMockBuilder('CommerceGuys\Addressing\Model\Subdivision')
            ->getMock();
        $secondChild = $this
            ->getMockBuilder('CommerceGuys\Addressing\Model\Subdivision')
            ->getMock();
        $empty = new ArrayCollection();
        $children = new ArrayCollection([$firstChild, $secondChild]);

        $this->assertEquals(false, $this->subdivision->hasChildren());
        $this->assertEquals($empty, $this->subdivision->getChildren());
        $this->subdivision->setChildren($children);
        $this->assertEquals($children, $this->subdivision->getChildren());
        $this->assertEquals(true, $this->subdivision->hasChildren());
    }

    /**
     * @covers ::getCountryCode
     * @covers ::setCountryCode
     */
    public function testCountryCode()
    {
        $this->subdivision->setCountryCode('US');
        $this->assertEquals('US', $this->subdivision->getCountryCode());
    }

    /**
     * @covers ::getId
     * @covers ::setId
     */
    public function testId()
    {
        $this->subdivision->setId('US-CA');
        $this->assertEquals('US-CA', $this->subdivision->getId());
    }

    /**
     * @covers ::getCode
     * @covers ::setCode
     */
    public function testCode()
    {
        $this->subdivision->setCode('CA');
        $this->assertEquals('CA', $this->subdivision->getCode());
    }

    /**
     * @covers ::getName
     * @covers ::setName
     */
    public function testName()
    {
        $this->subdivision->setName('California');
        $this->assertEquals('California', $this->subdivision->getName());
    }

    /**
     * @covers ::getPostalCodePattern
     * @covers ::setPostalCodePattern
     */
    public function testPostalCodePattern()
    {
        $this->subdivision->setPostalCodePattern('9[0-5]|96[01]');
        $this->assertEquals('9[0-5]|96[01]', $this->subdivision->getPostalCodePattern());
    }

    /**
     * @covers ::getPostalCodePatternType
     * @covers ::setPostalCodePatternType
     */
    public function testPostalCodePatternType()
    {
        $this->subdivision->setPostalCodePatternType(PatternType::START);
        $this->assertEquals(PatternType::START, $this->subdivision->getPostalCodePatternType());
    }

    /**
     * @covers ::getLocale
     * @covers ::setLocale
     */
    public function testLocale()
    {
        $this->subdivision->setLocale('en');
        $this->assertEquals('en', $this->subdivision->getLocale());
    }
}
