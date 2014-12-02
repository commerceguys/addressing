<?php

namespace CommerceGuys\Addressing\Tests\Model;

use CommerceGuys\Addressing\Model\Subdivision;

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
     * @covers ::getRepository
     * @covers ::setRepository
     * @uses \CommerceGuys\Addressing\Repository\SubdivisionRepository
     */
    public function testRepository()
    {
        $realRepository = $this->subdivision->getRepository();
        $this->assertInstanceOf('CommerceGuys\Addressing\Repository\SubdivisionRepository', $realRepository);

        // Replace the repository with a mock.
        $subdivisionRepository = $this
            ->getMockBuilder('CommerceGuys\Addressing\Repository\SubdivisionRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $this->subdivision->setRepository($subdivisionRepository);
        $this->assertEquals($subdivisionRepository, $this->subdivision->getRepository());
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
     * @covers ::getChildren
     * @covers ::setChildren
     * @covers ::hasChildren
     * @covers ::addChild
     * @covers ::removeChild
     * @covers ::hasChild
     */
    public function testChildren()
    {
        $firstChild = $this
            ->getMockBuilder('CommerceGuys\Addressing\Model\Subdivision')
            ->getMock();
        $secondChild = $this
            ->getMockBuilder('CommerceGuys\Addressing\Model\Subdivision')
            ->getMock();

        $this->assertEquals(false, $this->subdivision->hasChildren());
        $children = array($firstChild, $secondChild);
        $this->subdivision->setChildren($children);
        $this->assertEquals($children, $this->subdivision->getChildren());
        $this->assertEquals(true, $this->subdivision->hasChildren());
        $this->subdivision->removeChild($secondChild);
        $this->assertEquals(array($firstChild), $this->subdivision->getChildren());
        $this->assertEquals(false, $this->subdivision->hasChild($secondChild));
        $this->assertEquals(true, $this->subdivision->hasChild($firstChild));
        $this->subdivision->addChild($secondChild);
        $this->assertEquals($children, $this->subdivision->getChildren());
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
     * @covers ::getLocale
     * @covers ::setLocale
     */
    public function testLocale()
    {
        $this->subdivision->setLocale('en');
        $this->assertEquals('en', $this->subdivision->getLocale());
    }
}
