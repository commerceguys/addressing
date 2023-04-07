<?php

namespace CommerceGuys\Addressing\Tests\Subdivision;

use CommerceGuys\Addressing\Subdivision\LazySubdivisionCollection;
use PHPUnit\Framework\TestCase;
use CommerceGuys\Addressing\Subdivision\SubdivisionRepository;
use CommerceGuys\Addressing\Subdivision\Subdivision;

/**
 * @coversDefaultClass \CommerceGuys\Addressing\Subdivision\LazySubdivisionCollection
 */
final class LazySubdivisionCollectionTest extends TestCase
{
    /**
     * @var LazySubdivisionCollection
     */
    protected LazySubdivisionCollection $collection;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->collection = new LazySubdivisionCollection(['BR', 'Porto Acre']);
    }

    /**
     * @covers ::__construct
     */
    public function testConstructor(): void
    {
        $collection = new LazySubdivisionCollection(['BR', 'Porto Acre']);

        $reflected_constraint = (new \ReflectionObject($collection))->getProperty('parents');
        $reflected_constraint->setAccessible(true);
        $this->assertEquals(['BR', 'Porto Acre'], $reflected_constraint->getValue($collection));
    }

    /**
     * @covers ::doInitialize
     */
    public function testInitialize(): void
    {
        $subdivision = $this
            ->getMockBuilder(Subdivision::class)
            ->disableOriginalConstructor()
            ->getMock();
        $subdivisionRepository = $this
            ->getMockBuilder(SubdivisionRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $subdivisionRepository
            ->method('getAll')
            ->with(['BR', 'Porto Acre'])
            ->willReturn([$subdivision]);
        $this->collection->setRepository($subdivisionRepository);

        $this->assertFalse($this->collection->isInitialized());
        $this->assertCount(1, $this->collection);
        $this->assertTrue($this->collection->isInitialized());
    }

    /**
     * @covers ::getRepository
     * @covers ::setRepository
     */
    public function testRepository(): void
    {
        $subdivisionRepository = $this
            ->getMockBuilder(SubdivisionRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->collection->setRepository($subdivisionRepository);
        $this->assertSame($subdivisionRepository, $this->collection->getRepository());
    }
}
