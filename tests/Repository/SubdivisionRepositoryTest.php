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
    protected $subdivisionsES = array(
        'ES-O' => array(
            'locale' => 'es',
            'country_code' => 'ES',
            'parent_id' => null,
            'id' => 'ES-O',
            'code' => 'Asturias',
            'name' => 'Asturias',
            'postal_code_pattern' => '33',
            'has_children' => true,
            'translations' => array(
                'asturian' => array(
                    'name' => 'Asturies',
                ),
            ),
        ),
    );

    protected $subdivisionsESWithParent = array(
        'ES-O-Oviedo' => array(
            'locale' => 'es',
            'country_code' => 'ES',
            'parent_id' => 'ES-O',
            'id' => 'ES-O-Oviedo',
            'code' => 'Oviedo',
            'name' => 'Oviedo',
        ),
    );

    /**
     * Fake subdivision without parts.
     *
     * @var array
     */
    protected $invalidSubdivisions = array(
        'FAKE' => array(),
    );

    /**
     * @covers ::__construct
     */
    public function testConstructor()
    {
        // Mock the existence of JSON definitions on the filesystem.
        $root = vfsStream::setup('resources');
        vfsStream::newFile('subdivision/ES.json')->at($root)->setContent(
            json_encode($this->subdivisionsES)
        );
        vfsStream::newFile('subdivision/ES-O.json')->at($root)->setContent(
            json_encode($this->subdivisionsESWithParent)
        );
        vfsStream::newFile('subdivision/FA-KE.json')->at($root)->setContent(
            json_encode($this->invalidSubdivisions)
        );

        // Instantiate the subdivision repository and confirm that the
        // definition path was properly set.
        $subdivisionRepository = new SubdivisionRepository('vfs://resources/subdivision/');
        $definitionPath = $this->getObjectAttribute($subdivisionRepository, 'definitionPath');
        $this->assertEquals($definitionPath, 'vfs://resources/subdivision/');

        return $subdivisionRepository;
    }

    /**
     * @covers ::get
     * @covers ::loadDefinitions
     * @covers ::translateDefinition
     * @covers ::createSubdivisionFromDefinition
     * @depends testConstructor
     */
    public function testGet($subdivisionRepository)
    {
        $subdivision = $subdivisionRepository->get('ES-O');
        $this->assertInstanceOf('CommerceGuys\Addressing\Model\Subdivision', $subdivision);
    }

    /**
     * @covers ::get
     * @covers ::loadDefinitions
     * @covers ::translateDefinition
     * @covers ::createSubdivisionFromDefinition
     * @depends testConstructor
     */
    public function testGetSubdivisionWithLocale($subdivisionRepository)
    {
        $subdivision = $subdivisionRepository->get('ES-O', 'es');
        $this->assertInstanceOf('CommerceGuys\Addressing\Model\Subdivision', $subdivision);
    }

    /**
     * @covers ::get
     * @covers ::loadDefinitions
     * @covers ::translateDefinition
     * @covers ::createSubdivisionFromDefinition
     * @depends testConstructor
     */
    public function testGetSubdivisionWithTranslation($subdivisionRepository)
    {
        $subdivision = $subdivisionRepository->get('ES-O', 'asturian');
        $this->assertInstanceOf('CommerceGuys\Addressing\Model\Subdivision', $subdivision);
        $subdivisionName = $this->getObjectAttribute($subdivision, 'name');
        $this->assertEquals('Asturies', $subdivisionName);
    }

    /**
     * @covers ::get
     * @covers ::loadDefinitions
     * @covers ::translateDefinition
     * @covers ::createSubdivisionFromDefinition
     * @depends testConstructor
     */
    public function testGetSubdivisionWithParents($subdivisionRepository)
    {
        $subdivision = $subdivisionRepository->get('ES-O-Oviedo');
        $this->assertInstanceOf('CommerceGuys\Addressing\Model\Subdivision', $subdivision);
    }

    /**
     * @covers ::get
     * @covers ::loadDefinitions
     * @covers ::translateDefinition
     * @covers ::createSubdivisionFromDefinition
     * @depends testConstructor
     */
    public function testGetOrphanSubdivision($subdivisionRepository)
    {
        $subdivision = $subdivisionRepository->get('ZZ-ZZ');
        $this->assertNull($subdivision);
    }

    /**
     * @covers ::get
     * @covers ::loadDefinitions
     * @covers ::translateDefinition
     * @covers ::createSubdivisionFromDefinition
     * @depends testConstructor
     */
    public function testGetInvalidSubdivision($subdivisionRepository)
    {
        $subdivision = $subdivisionRepository->get('FAKE');
        $this->assertNull($subdivision);
    }

    /**
     * @covers ::getAll
     * @covers ::loadDefinitions
     * @covers ::translateDefinition
     * @covers ::createSubdivisionFromDefinition
     * @depends testConstructor
     */
    public function testGetAll($subdivisionRepository)
    {
        $subdivisions = $subdivisionRepository->getAll('ES');
        $this->assertInternalType('array', $subdivisions);
        foreach ($subdivisions as $subdivision) {
            $this->assertInstanceOf('CommerceGuys\Addressing\Model\Subdivision', $subdivision);
        }
    }
}
