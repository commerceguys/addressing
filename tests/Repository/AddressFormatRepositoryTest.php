<?php

namespace CommerceGuys\Addressing\Tests\Repository;

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
        vfsStream::newFile('address_format/ES.json')->at($root)->setContent(
            json_encode($this->addressFormatES)
        );
        vfsStream::newFile('address_format/ZZ.json')->at($root)->setContent(
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
     * @depends testConstructor
     */
    public function testGetExistingAddressFormat($addressFormatRepository)
    {
        $addressFormat = $addressFormatRepository->get('ES');
        $this->assertInstanceOf('CommerceGuys\Addressing\Model\AddressFormat', $addressFormat);
    }

    /**
     * @covers ::get
     * @covers ::loadDefinition
     * @covers ::translateDefinition
     * @covers ::createAddressFormatFromDefinition
     * @depends testConstructor
     */
    public function testGetNonExistingAddressFormat($addressFormatRepository)
    {
        $addressFormat = $addressFormatRepository->get('Kitten');
        $countryCode = $this->getObjectAttribute($addressFormat, 'countryCode');
        $this->assertEquals('ZZ', $countryCode);
    }
}
