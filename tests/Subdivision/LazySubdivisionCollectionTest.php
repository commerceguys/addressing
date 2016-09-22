<?php

namespace CommerceGuys\Addressing\Tests\Subdivision;

use CommerceGuys\Addressing\Subdivision\LazySubdivisionCollection;

/**
 * @coversDefaultClass \CommerceGuys\Addressing\Subdivision\LazySubdivisionCollection
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
        $this->collection = new LazySubdivisionCollection(['BR', 'Porto Acre']);
    }

    /**
     * @covers ::__construct
     */
    public function testConstructor()
    {
        $collection = new LazySubdivisionCollection(['BR', 'Porto Acre']);
        $this->assertEquals(['BR', 'Porto Acre'], $this->getObjectAttribute($collection, 'parents'));
    }

    /**
     * @covers ::doInitialize
     */
    public function testInitialize()
    {
        $subdivision = $this
            ->getMockBuilder('CommerceGuys\Addressing\Subdivision\Subdivision')
            ->disableOriginalConstructor()
            ->getMock();
        $subdivisionRepository = $this
            ->getMockBuilder('CommerceGuys\Addressing\Subdivision\SubdivisionRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $subdivisionRepository
            ->expects($this->any())
            ->method('getAll')
            ->with(['BR', 'Porto Acre'])
            ->will($this->returnValue([$subdivision]));
        $this->collection->setRepository($subdivisionRepository);

        $this->assertFalse($this->collection->isInitialized());
        $this->assertCount(1, $this->collection);
        $this->assertTrue($this->collection->isInitialized());
    }

    /**
     * @covers ::getRepository
     * @covers ::setRepository
     */
    public function testRepository()
    {
        $subdivisionRepository = $this
            ->getMockBuilder('CommerceGuys\Addressing\Subdivision\SubdivisionRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $this->collection->setRepository($subdivisionRepository);
        $this->assertSame($subdivisionRepository, $this->collection->getRepository());
    }
}
