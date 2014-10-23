<?php

namespace CommerceGuys\Addressing\Tests\Repository;

use CommerceGuys\Addressing\Model\AddressFormat;
use CommerceGuys\Addressing\Repository\AddressFormatRepository;
use org\bovigo\vfs\vfsStream;

/**
 * @coversDefaultClass \CommerceGuys\Addressing\Repository\AddressFormatRepository
 */
class AddressFormatRepositoryTest extends \PHPUnit_Framework_TestCase
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
     * @covers ::__construct
     */
    public function testConstructor()
    {
        // Mock the existence of JSON definitions on the filesystem.
        $root = vfsStream::setup('resources');
        $directory = vfsStream::newDirectory('address_format')->at($root);
        vfsStream::newFile('ES.json')->at($directory)->setContent(
            json_encode($this->addressFormatES)
        );
        vfsStream::newFile('ZZ.json')->at($directory)->setContent(
            json_encode($this->addressFormatZZ)
        );

        // Instantiate the address format repository and confirm that the
        // definition path was properly set.
        $addressFormatRepository = new AddressFormatRepository('vfs://resources/address_format/');
        $definitionPath = $this->getObjectAttribute($addressFormatRepository, 'definitionPath');
        $this->assertEquals($definitionPath, 'vfs://resources/address_format/');

        return $addressFormatRepository;
    }

    /**
     * @covers ::get
     * @covers ::loadDefinition
     * @covers ::translateDefinition
     * @covers ::createAddressFormatFromDefinition
     * @uses \CommerceGuys\Addressing\Model\AddressFormat
     * @depends testConstructor
     */
    public function testGet($addressFormatRepository)
    {
        $requiredFields = array(
            AddressFormat::FIELD_ADDRESS,
            AddressFormat::FIELD_LOCALITY,
            AddressFormat::FIELD_ADMINISTRATIVE_AREA,
            AddressFormat::FIELD_POSTAL_CODE,

        );
        $uppercaseFields = array(
            AddressFormat::FIELD_LOCALITY,
            AddressFormat::FIELD_ADMINISTRATIVE_AREA,
        );

        $addressFormat = $addressFormatRepository->get('ES');
        $this->assertInstanceOf('CommerceGuys\Addressing\Model\AddressFormat', $addressFormat);
        $this->assertEquals('ES', $addressFormat->getCountryCode());
        $this->assertEquals($requiredFields, $addressFormat->getRequiredFields());
        $this->assertEquals($uppercaseFields, $addressFormat->getUppercaseFields());
        $this->assertEquals(AddressFormat::ADMINISTRATIVE_AREA_TYPE_PROVINCE, $addressFormat->getAdministrativeAreaType());
        $this->assertEquals(AddressFormat::POSTAL_CODE_TYPE_POSTAL, $addressFormat->getPostalCodeType());
        $this->assertEquals($this->addressFormatES['format'], $addressFormat->getFormat());
        $this->assertEquals($this->addressFormatES['postal_code_pattern'], $addressFormat->getPostalCodePattern());
        $this->assertEquals($this->addressFormatES['postal_code_prefix'], $addressFormat->getPostalCodePrefix());
        $this->assertEquals($this->addressFormatES['locale'], $addressFormat->getLocale());
    }

    /**
     * @covers ::get
     * @covers ::loadDefinition
     * @covers ::translateDefinition
     * @covers ::createAddressFormatFromDefinition
     * @uses \CommerceGuys\Addressing\Model\AddressFormat
     * @depends testConstructor
     */
    public function testGetNonExistingAddressFormat($addressFormatRepository)
    {
        $addressFormat = $addressFormatRepository->get('Kitten');
        $this->assertEquals('ZZ', $addressFormat->getCountryCode());
    }

    /**
     * @covers ::getAll
     * @covers ::loadDefinition
     * @covers ::translateDefinition
     * @covers ::createAddressFormatFromDefinition
     * @uses \CommerceGuys\Addressing\Repository\AddressFormatRepository::get
     * @uses \CommerceGuys\Addressing\Model\AddressFormat
     * @depends testConstructor
     */
    public function testGetAll($addressFormatRepository)
    {
        $addressFormats = $addressFormatRepository->getAll();
        $this->assertArrayHasKey('ES', $addressFormats);
        $this->assertArrayHasKey('ZZ', $addressFormats);
        $this->assertEquals($addressFormats['ES']->getCountryCode(), 'ES');
        $this->assertEquals($addressFormats['ZZ']->getCountryCode(), 'ZZ');
    }
}
