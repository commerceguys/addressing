<?php

namespace CommerceGuys\Addressing\Tests\Collection;

use CommerceGuys\Addressing\Collection\LazySubdivisionCollection;

/**
 * @coversDefaultClass \CommerceGuys\Addressing\Collection\LazySubdivisionCollection
 */
class LazySubdivisionCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LazySubdivisionCollection
     */
    protected $collection;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->collection = new LazySubdivisionCollection('BR', 'BR-AC-6e6b33', 'pt');
    }

    /**
     * @covers ::__construct
     */
    public function testConstructor()
    {
        $collection = new LazySubdivisionCollection('BR', 'BR-AC-6e6b33', 'pt');
        $this->assertEquals('BR', $this->getObjectAttribute($collection, 'countryCode'));
        $this->assertEquals('BR-AC-6e6b33', $this->getObjectAttribute($collection, 'parentId'));
        $this->assertEquals('pt', $this->getObjectAttribute($collection, 'locale'));
    }

    /**
     * @covers ::doInitialize
     *
     * @uses \CommerceGuys\Addressing\Collection\LazySubdivisionCollection::__construct
     * @uses \CommerceGuys\Addressing\Collection\LazySubdivisionCollection::getRepository
     * @uses \CommerceGuys\Addressing\Collection\LazySubdivisionCollection::setRepository
     */
    public function testInitialize()
    {
        $subdivision = $this
            ->getMockBuilder('CommerceGuys\Addressing\Model\Subdivision')
            ->disableOriginalConstructor()
            ->getMock();
        $subdivisionRepository = $this
            ->getMockBuilder('CommerceGuys\Addressing\Repository\SubdivisionRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $subdivisionRepository
            ->expects($this->any())
            ->method('getAll')
            ->with('BR', 'BR-AC-6e6b33', 'pt')
            ->will($this->returnValue([$subdivision]));
        $this->collection->setRepository($subdivisionRepository);

        $this->assertFalse($this->collection->isInitialized());
        $this->assertCount(1, $this->collection);
        $this->assertTrue($this->collection->isInitialized());
    }

    /**
     * @covers ::getRepository
     * @covers ::setRepository
     *
     * @uses \CommerceGuys\Addressing\Collection\LazySubdivisionCollection::__construct
     */
    public function testRepository()
    {
        $subdivisionRepository = $this
            ->getMockBuilder('CommerceGuys\Addressing\Repository\SubdivisionRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $this->collection->setRepository($subdivisionRepository);
        $this->assertSame($subdivisionRepository, $this->collection->getRepository());
    }
}
