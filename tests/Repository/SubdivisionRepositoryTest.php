<?php

namespace CommerceGuys\Addressing\Tests\Repository;

use CommerceGuys\Addressing\Model\Subdivision;
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
            'BR-SC' => [
                'locale' => 'pt',
                'country_code' => 'BR',
                'parent_id' => null,
                'id' => 'BR-SC',
                'code' => 'SC',
                'name' => 'Santa Catarina',
                'postal_code_pattern' => '8[89]',
                'has_children' => true,
            ],
            'BR-SP' => [
                'locale' => 'pt',
                'country_code' => 'BR',
                'parent_id' => null,
                'id' => 'BR-SP',
                'code' => 'SP',
                'name' => 'São Paulo',
                'postal_code_pattern' => '[01][1-9]',
                'has_children' => true,
            ],
        ],
        'BR-SC' => [
            'BR-SC-9c7753' => [
                'locale' => 'pt',
                'country_code' => 'BR',
                'parent_id' => 'BR-SC',
                'id' => 'BR-SC-9c7753',
                'code' => 'Abelardo Luz',
                'name' => 'Abelardo Luz',
            ],
        ],
        'BR-SP' => [
            'BR-SP-8e3f19' => [
                'locale' => 'pt',
                'country_code' => 'BR',
                'parent_id' => 'BR-SP',
                'id' => 'BR-SP-8e3f19',
                'code' => 'Anhumas',
                'name' => 'Anhumas',
            ],
        ],
    ];

    /**
     * @covers ::__construct
     */
    public function testConstructor()
    {
        // Mock the existence of JSON definitions on the filesystem.
        $root = vfsStream::setup('resources');
        $directory = vfsStream::newDirectory('subdivision')->at($root);
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
     * @covers ::get
     * @covers ::loadDefinitions
     * @covers ::createSubdivisionFromDefinition
     * @uses \CommerceGuys\Addressing\Repository\SubdivisionRepository::__construct
     * @uses \CommerceGuys\Addressing\Repository\SubdivisionRepository::getAll
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
        $this->assertEquals('pt', $subdivision->getLocale());
        $children = $subdivision->getChildren();
        $this->assertEquals($subdivisionChild, $children['BR-SC-9c7753']);

        $this->assertInstanceOf('CommerceGuys\Addressing\Model\Subdivision', $subdivisionChild);
        $this->assertEquals('BR-SC-9c7753', $subdivisionChild->getId());
        // $subdivision contains the loaded children while $parent doesn't,
        // so they can't be compared directly.
        $parent = $subdivisionChild->getParent();
        $this->assertInstanceOf('CommerceGuys\Addressing\Model\Subdivision', $parent);
        $this->assertEquals($subdivision->getId(), $parent->getId());
    }

    /**
     * @covers ::get
     * @covers ::loadDefinitions
     * @covers ::createSubdivisionFromDefinition
     * @uses \CommerceGuys\Addressing\Repository\DefinitionTranslatorTrait
     * @depends testConstructor
     */
    public function testGetInvalidSubdivision($subdivisionRepository)
    {
        $invalidIds = ['FAKE', 'ES-A', 'BR-SC-FAKE'];
        foreach ($invalidIds as $invalidId) {
            $subdivision = $subdivisionRepository->get($invalidId);
            $this->assertNull($subdivision);
        }
    }

    /**
     * @covers ::getAll
     * @covers ::loadDefinitions
     * @covers ::createSubdivisionFromDefinition
     * @uses \CommerceGuys\Addressing\Repository\DefinitionTranslatorTrait
     * @uses \CommerceGuys\Addressing\Model\Subdivision
     * @uses \CommerceGuys\Addressing\Collection\LazySubdivisionCollection
     * @depends testConstructor
     */
    public function testGetAll($subdivisionRepository)
    {
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
}
