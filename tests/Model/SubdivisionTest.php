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
     * The US state of Texas.
     *
     * @var Subdivision
     */
    protected $texas;

    public function setUp()
    {
        $this->subdivision = new Subdivision();
    }

    /**
     * @covers ::getRepository
     * @covers ::setRepository
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
     * @covers ::getChildren
     * @covers ::setChildren
     * @covers ::hasChildren
     * @uses CommerceGuys\Addressing\Model\Subdivision::setId
     * @uses CommerceGuys\Addressing\Model\Subdivision::setCountryCode
     * @uses CommerceGuys\Addressing\Model\Subdivision::getCode
     * @uses CommerceGuys\Addressing\Model\Subdivision::setCode
     */
    public function testHierarchy()
    {
        // Create a mock repository.
        $subdivisionRepository = $this
            ->getMockBuilder('CommerceGuys\Addressing\Repository\SubdivisionRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $subdivisionRepository->expects($this->any())
          ->method('get')
          ->with('US-TX')
          ->will($this->returnValue($this->texas));
        // The US only has one level of subdivisions. For testing purposes,
        // make the repository return Texas as Califoria's child.
        $subdivisionRepository->expects($this->any())
          ->method('getAll')
          ->with($this->equalTo('US'), $this->equalTo('US-CA'))
          ->will($this->returnValue(array($this->texas)));
        $this->subdivision->setRepository($subdivisionRepository);

        $texasStub = new Subdivision();
        $texasStub->setCountryCode('US');
        $texasStub->setId('US-TX');
        // getParent() should detect the stub and load the full subdivision.
        $this->subdivision->setParent($texasStub);
        $this->assertEquals($this->texas, $this->subdivision->getParent());

        // Test lazy-loading of children.
        // This requires the subdivision id and country code to be set.
        $this->assertEquals(false, $this->subdivision->hasChildren());
        $this->subdivision->setCountryCode('US');
        $this->subdivision->setId('US-CA');
        $this->subdivision->setChildren(array('load'));
        $this->assertEquals(array($this->texas), $this->subdivision->getChildren());
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
     * @covers ::getLocale
     * @covers ::setLocale
     */
    public function testLocale()
    {
        $this->subdivision->setLocale('en');
        $this->assertEquals('en', $this->subdivision->getLocale());
    }
}
