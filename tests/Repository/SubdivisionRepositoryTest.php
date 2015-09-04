<?php

namespace CommerceGuys\Addressing\Tests\Repository;

use CommerceGuys\Addressing\Repository\SubdivisionRepository;
use org\bovigo\vfs\vfsStream;

/**
 * @coversDefaultClass \CommerceGuys\Addressing\Repository\SubdivisionRepository
 */
class SubdivisionRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Subdivisions.
     *
     * @var array
     */
    protected $subdivisions = [
        'BR' => [
            'country_code' => 'BR',
            'parent_id' => null,
            'locale' => 'pt',
            'subdivisions' => [
                'BR-SC' => [
                    'code' => 'SC',
                    'name' => 'Santa Catarina',
                    'postal_code_pattern' => '8[89]',
                    'postal_code_pattern_type' => 'full',
                    'has_children' => true,
                ],
                'BR-SP' => [
                    'code' => 'SP',
                    'name' => 'São Paulo',
                    'postal_code_pattern' => '[01][1-9]',
                    'has_children' => true,
                ],
            ],
        ],
        'BR-SC' => [
            'country_code' => 'BR',
            'parent_id' => 'BR-SC',
            'locale' => 'pt',
            'subdivisions' => [
                'BR-SC-9c7753' => [
                    'name' => 'Abelardo Luz',
                ],
            ],
        ],
        'BR-SP' => [
            'locale' => 'pt',
            'country_code' => 'BR',
            'parent_id' => 'BR-SP',
            'subdivisions' => [
                'BR-SP-8e3f19' => [
                    'name' => 'Anhumas',
                ],
            ]
        ],
    ];

    /**
     * Subdivision depths.
     *
     * @var array
     */
    protected $depths = [
        'BR' => 2,
    ];

    /**
     * @covers ::__construct
     */
    public function testConstructor()
    {
        // Mock the existence of JSON definitions on the filesystem.
        $root = vfsStream::setup('resources');
        $directory = vfsStream::newDirectory('subdivision')->at($root);
        vfsStream::newFile('depths.json')->at($directory)->setContent(json_encode($this->depths));
        foreach ($this->subdivisions as $parent => $data) {
            $filename = $parent . '.json';
            vfsStream::newFile($filename)->at($directory)->setContent(json_encode($data));
        }

        // Instantiate the subdivision repository and confirm that the
        // definition path was properly set.
        $subdivisionRepository = new SubdivisionRepository('vfs://resources/subdivision/');
        $definitionPath = $this->getObjectAttribute($subdivisionRepository, 'definitionPath');
        $this->assertEquals('vfs://resources/subdivision/', $definitionPath);

        return $subdivisionRepository;
    }

    /**
     * @covers ::getDepth
     *
     * @depends testConstructor
     */
    public function testGetDepth($subdivisionRepository)
    {
        $depth = $subdivisionRepository->getDepth('BR');
        $this->assertEquals(2, $depth);

        $depth = $subdivisionRepository->getDepth('RS');
        $this->assertEquals(0, $depth);
    }

    /**
     * @covers ::get
     * @covers ::loadDefinitions
     * @covers ::hasData
     * @covers ::createSubdivisionFromDefinitions
     *
     * @uses \CommerceGuys\Addressing\Repository\SubdivisionRepository::__construct
     * @uses \CommerceGuys\Addressing\Repository\SubdivisionRepository::getAll
     * @uses \CommerceGuys\Addressing\Repository\SubdivisionRepository::getDepth
     * @uses \CommerceGuys\Addressing\Repository\DefinitionTranslatorTrait
     * @uses \CommerceGuys\Addressing\Model\Subdivision
     * @uses \CommerceGuys\Addressing\Collection\LazySubdivisionCollection
     * @depends testConstructor
     */
    public function testGet($subdivisionRepository)
    {
        $subdivision = $subdivisionRepository->get('BR-SC');
        $subdivisionChild = $subdivisionRepository->get('BR-SC-9c7753');

        $this->assertInstanceOf('CommerceGuys\Addressing\Model\Subdivision', $subdivision);
        $this->assertEquals(null, $subdivision->getParent());
        $this->assertEquals('BR', $subdivision->getCountryCode());
        $this->assertEquals('BR-SC', $subdivision->getId());
        $this->assertEquals('SC', $subdivision->getCode());
        $this->assertEquals('Santa Catarina', $subdivision->getName());
        $this->assertEquals('8[89]', $subdivision->getPostalCodePattern());
        $this->assertEquals('full', $subdivision->getPostalCodePatternType());
        $this->assertEquals('pt', $subdivision->getLocale());
        $children = $subdivision->getChildren();
        $this->assertEquals($subdivisionChild, $children['BR-SC-9c7753']);

        $this->assertInstanceOf('CommerceGuys\Addressing\Model\Subdivision', $subdivisionChild);
        $this->assertEquals('BR-SC-9c7753', $subdivisionChild->getId());
        $this->assertEquals('Abelardo Luz', $subdivisionChild->getCode());
        // $subdivision contains the loaded children while $parent doesn't,
        // so they can't be compared directly.
        $parent = $subdivisionChild->getParent();
        $this->assertInstanceOf('CommerceGuys\Addressing\Model\Subdivision', $parent);
        $this->assertEquals($subdivision->getId(), $parent->getId());
    }

    /**
     * @covers ::get
     * @covers ::loadDefinitions
     * @covers ::hasData
     * @covers ::createSubdivisionFromDefinitions
     *
     * @uses \CommerceGuys\Addressing\Repository\SubdivisionRepository::getDepth
     * @uses \CommerceGuys\Addressing\Repository\DefinitionTranslatorTrait
     * @depends testConstructor
     */
    public function testGetInvalidSubdivision($subdivisionRepository)
    {
        $invalidIds = ['FAKE', 'ES-A', 'BR-SC-FAKE', 'BR-FK-FAKE'];
        foreach ($invalidIds as $invalidId) {
            $subdivision = $subdivisionRepository->get($invalidId);
            $this->assertNull($subdivision);
        }
    }

    /**
     * @covers ::getAll
     * @covers ::loadDefinitions
     * @covers ::hasData
     * @covers ::createSubdivisionFromDefinitions
     *
     * @uses \CommerceGuys\Addressing\Repository\SubdivisionRepository::getDepth
     * @uses \CommerceGuys\Addressing\Repository\DefinitionTranslatorTrait
     * @uses \CommerceGuys\Addressing\Model\Subdivision
     * @uses \CommerceGuys\Addressing\Collection\LazySubdivisionCollection
     * @depends testConstructor
     */
    public function testGetAll($subdivisionRepository)
    {
        $subdivisions = $subdivisionRepository->getAll('RS');
        $this->assertEquals([], $subdivisions);

        $subdivisions = $subdivisionRepository->getAll('BR');
        $this->assertCount(2, $subdivisions);
        $this->assertArrayHasKey('BR-SC', $subdivisions);
        $this->assertArrayHasKey('BR-SP', $subdivisions);
        $this->assertEquals($subdivisions['BR-SC']->getId(), 'BR-SC');
        $this->assertEquals($subdivisions['BR-SP']->getId(), 'BR-SP');

        $subdivisions = $subdivisionRepository->getAll('BR', 'BR-SC');
        $this->assertCount(1, $subdivisions);
        $this->assertArrayHasKey('BR-SC-9c7753', $subdivisions);
        $this->assertEquals($subdivisions['BR-SC-9c7753']->getId(), 'BR-SC-9c7753');
    }

    /**
     * @covers ::getList
     * @covers ::loadDefinitions
     * @covers ::hasData
     *
     * @uses \CommerceGuys\Addressing\Repository\SubdivisionRepository::getDepth
     * @uses \CommerceGuys\Addressing\Repository\DefinitionTranslatorTrait
     * @depends testConstructor
     */
    public function testGetList($subdivisionRepository)
    {
        $list = $subdivisionRepository->getList('RS');
        $this->assertEquals([], $list);

        $list = $subdivisionRepository->getList('BR');
        $expectedList = ['BR-SC' => 'Santa Catarina', 'BR-SP' => 'São Paulo'];
        $this->assertEquals($expectedList, $list);

        $list = $subdivisionRepository->getList('BR', 'BR-SC');
        $expectedList = ['BR-SC-9c7753' => 'Abelardo Luz'];
        $this->assertEquals($expectedList, $list);
    }
}
