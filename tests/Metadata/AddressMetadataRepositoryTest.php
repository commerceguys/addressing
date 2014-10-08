<?php

namespace CommerceGuys\Addressing\Tests\Metadata;

use CommerceGuys\Addressing\Metadata\AddressMetadataRepository;
use org\bovigo\vfs\vfsStream;

/**
 * @coversDefaultClass \CommerceGuys\Addressing\Metadata\AddressMetadataRepository
 */
class AddressMetadataRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Known address format.
     *
     * @var array
     */
    protected $addressFormatES = array(
        'locale' => 'es',
        'format' => '%recipient\n%organization\n%address\n%postal_code %locality %administrative_area',
        'required_fields' => array(
            'address',
            'locality',
            'administrative_area',
            'postal_code',
        ),
        'uppercase_fields' => array(
            'locality',
            'administrative_area',
        ),
        'administrative_area_type' => 'province',
        'postal_code_type' => 'postal',
        'postal_code_pattern' => '\\d{5}',
        'postal_code_prefix' => 'A',
    );

    /**
     * Fallback address format.
     *
     * @var array
     */
    protected $addressFormatZZ = array(
        'locale' => 'und',
        'format' => '%recipient\n%organization\n%address\n%locality',
        'required_fields' => array(
            'address',
            'locality',
        ),
        'uppercase_fields' => array(
            'locality',
        ),
        'administrative_area_type' => 'province',
        'postal_code_type' => 'postal',
    );

    /**
     * Subdivisions.
     *
     * @var array
     */
    protected $subDivisionsES = array(
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

    protected $subDivisionsESWithParent = array(
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
    protected $invalidSubDivisions = array(
        'FAKE' => array(),
    );

    /**
     * English country definitions.
     *
     * @var array
     */
    protected $englishDefinitions = array(
        'FR' => array(
            'name' => 'France',
        ),
        'US' => array(
            'name' => 'United States',
        ),
    );

    /**
     * Address Metadata Repository.
     *
     * @var \CommerceGuys\Addressing\Metadata\AddressMetadataRepository
     */
    protected $addressMetadataRepository;

    public function setUp()
    {
        // Mock the existence of JSON definitions on the filesystem.
        $root = vfsStream::setup('resources');
        vfsStream::newFile('address_format/ES.json')->at($root)->setContent(
          json_encode($this->addressFormatES)
        );
        vfsStream::newFile('address_format/ZZ.json')->at($root)->setContent(
          json_encode($this->addressFormatZZ)
        );
        vfsStream::newFile('subdivision/ES.json')->at($root)->setContent(
          json_encode($this->subDivisionsES)
        );
        vfsStream::newFile('subdivision/ES-O.json')->at($root)->setContent(
          json_encode($this->subDivisionsESWithParent)
        );
        vfsStream::newFile('subdivision/FA-KE.json')->at($root)->setContent(
          json_encode($this->invalidSubDivisions)
        );

        foreach ($this->englishDefinitions as $definition) {
            // Get a stub for the Country class.
            $country = $this->getMock('CommerceGuys\Intl\Country\Country');
            // Configure the stub.
            $country->expects($this->any())
              ->method('getName')
              ->will($this->returnValue($definition['name']));
            $countries[] = $country;
        }

        // Create a stub for the CountryRepository class.
        $countryRepository = $this->getMockBuilder(
          'CommerceGuys\Intl\Country\CountryRepository'
        )
          ->disableOriginalConstructor()
          ->getMock();

        // Configure the stub.
        $countryRepository->expects($this->any())
          ->method('get')
          ->with('FR')
          ->will($this->returnValue($countries[0]));
        $countryRepository->expects($this->any())
          ->method('getAll')
          ->will($this->returnValue($countries));

        // Instantiate the address metadata repository and confirm that the
        // definition path and the country repository have been properly set.
        $this->addressMetadataRepository = new addressMetadataRepository(
          'vfs://resources/', $countryRepository
        );
    }

    /**
     * @covers ::__construct
     */
    public function testConstructor()
    {
        $definitionPath = $this->getObjectAttribute(
          $this->addressMetadataRepository,
          'definitionPath'
        );
        $this->assertEquals($definitionPath, 'vfs://resources/');
        $countryRepository = $this->getObjectAttribute(
          $this->addressMetadataRepository,
          'countryRepository'
        );
        $this->assertInstanceOf(
          'CommerceGuys\Intl\Country\CountryRepository',
          $countryRepository
        );
    }

    /**
     * @covers ::getCountryName
     * @uses \CommerceGuys\Intl\Country\CountryRepository::get
     */
    public function testGetCountryName()
    {
        $countryName = $this->addressMetadataRepository->getCountryName('FR');
        $this->assertEquals('France', $countryName);
    }

    /**
     * @covers ::getCountryNames
     * @uses \CommerceGuys\Intl\Country\CountryRepository::getAll
     */
    public function testGetCountryNames()
    {
        $countries = array();
        foreach ($this->englishDefinitions as $definition) {
            $countries[] = $definition['name'];
        }
        $countryNames = $this->addressMetadataRepository->getCountryNames();
        $this->assertEquals($countries, $countryNames);
    }

    /**
     * @covers ::getAddressFormat
     * @covers ::loadAddressFormatDefinition
     * @covers ::translateDefinition
     * @covers ::createAddressFormatFromDefinition
     */
    public function testGetExistingAddressFormat()
    {
        $addressFormat = $this->addressMetadataRepository->getAddressFormat(
          'ES'
        );
        $this->assertInstanceOf('CommerceGuys\Addressing\Metadata\AddressFormat', $addressFormat);
    }

    /**
     * @covers ::getAddressFormat
     * @covers ::loadAddressFormatDefinition
     * @covers ::translateDefinition
     * @covers ::createAddressFormatFromDefinition
     */
    public function testGetNonExistingAddressFormat()
    {
        $addressFormat = $this->addressMetadataRepository->getAddressFormat(
          'Kitten'
        );
        $countryCode = $this->getObjectAttribute(
          $addressFormat,
          'countryCode'
        );
        $this->assertEquals('ZZ', $countryCode);
    }

    /**
     * @covers ::getSubdivision
     * @covers ::loadSubdivisionDefinitions
     * @covers ::translateDefinition
     * @covers ::createSubdivisionFromDefinition
     */
    public function testGetSubdivision()
    {
        $subDivision = $this->addressMetadataRepository->getSubdivision('ES-O');
        $this->assertInstanceOf('CommerceGuys\Addressing\Metadata\Subdivision', $subDivision);
    }

    /**
     * @covers ::getSubdivision
     * @covers ::loadSubdivisionDefinitions
     * @covers ::translateDefinition
     * @covers ::createSubdivisionFromDefinition
     */
    public function testGetSubdivisionWithLocale()
    {
        $subDivision = $this->addressMetadataRepository->getSubdivision('ES-O', 'es');
        $this->assertInstanceOf('CommerceGuys\Addressing\Metadata\Subdivision', $subDivision);
    }

    /**
     * @covers ::getSubdivision
     * @covers ::loadSubdivisionDefinitions
     * @covers ::translateDefinition
     * @covers ::createSubdivisionFromDefinition
     */
    public function testGetSubdivisionWithTranslation()
    {
        $subDivision = $this->addressMetadataRepository->getSubdivision('ES-O', 'asturian');
        $this->assertInstanceOf('CommerceGuys\Addressing\Metadata\Subdivision', $subDivision);
        $subDivisionName = $this->getObjectAttribute(
          $subDivision,
          'name'
        );
        $this->assertEquals('Asturies', $subDivisionName);
    }

    /**
     * @covers ::getSubdivision
     * @covers ::loadSubdivisionDefinitions
     * @covers ::translateDefinition
     * @covers ::createSubdivisionFromDefinition
     */
    public function testGetSubdivisionWithParents()
    {
        $subDivision = $this->addressMetadataRepository->getSubdivision('ES-O-Oviedo');
        $this->assertInstanceOf('CommerceGuys\Addressing\Metadata\Subdivision', $subDivision);
    }

    /**
     * @covers ::getSubdivision
     * @covers ::loadSubdivisionDefinitions
     * @covers ::translateDefinition
     * @covers ::createSubdivisionFromDefinition
     */
    public function testGetOrphanSubdivision()
    {
        $subDivision = $this->addressMetadataRepository->getSubdivision('ZZ-ZZ');
        $this->assertNull($subDivision);
    }

    /**
     * @covers ::getSubdivision
     * @covers ::loadSubdivisionDefinitions
     * @covers ::translateDefinition
     * @covers ::createSubdivisionFromDefinition
     */
    public function testGetInvalidSubdivision()
    {
        $subDivision = $this->addressMetadataRepository->getSubdivision('FAKE');
        $this->assertNull($subDivision);
    }

    /**
     * @covers ::getSubdivisions
     * @covers ::loadSubdivisionDefinitions
     * @covers ::translateDefinition
     * @covers ::createSubdivisionFromDefinition
     */
    public function testGetSubdivisions()
    {
        $subDivisions = $this->addressMetadataRepository->getSubdivisions('ES');
        $this->assertInternalType('array', $subDivisions);
        foreach ($subDivisions as $subDivision) {
            $this->assertInstanceOf('CommerceGuys\Addressing\Metadata\Subdivision', $subDivision);
        }
    }

}
