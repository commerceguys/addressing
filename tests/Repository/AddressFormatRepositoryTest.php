<?php

namespace CommerceGuys\Addressing\Tests\Repository;

use CommerceGuys\Addressing\Enum\AddressField;
use CommerceGuys\Addressing\Enum\AdministrativeAreaType;
use CommerceGuys\Addressing\Enum\DependentLocalityType;
use CommerceGuys\Addressing\Enum\LocalityType;
use CommerceGuys\Addressing\Enum\PostalCodeType;
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
    protected $addressFormatES = [
        'locale' => 'es',
        'format' => '%recipient\n%organization\n%addressLine1\n%addressLine2\n%postalCode %locality %administrativeArea',
        'required_fields' => [
            'addressLine1',
            'locality',
            'administrativeArea',
            'postalCode',
        ],
        'uppercase_fields' => [
            'locality',
            'administrativeArea',
        ],
        'administrative_area_type' => 'province',
        'locality_type' => 'city',
        'dependent_locality_type' => 'suburb',
        'postal_code_type' => 'postal',
        'postal_code_pattern' => '\\d{5}',
        'postal_code_prefix' => 'A',
    ];

    /**
     * Fallback address format.
     *
     * @var array
     */
    protected $addressFormatZZ = [
        'locale' => 'und',
        'format' => '%recipient\n%organization\n%address\n%locality',
        'required_fields' => [
            'address',
            'locality',
        ],
        'uppercase_fields' => [
            'locality',
        ],
        'administrative_area_type' => 'province',
        'locality_type' => 'city',
        'dependent_locality_type' => 'suburb',
        'postal_code_type' => 'postal',
    ];

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
        $this->assertEquals('vfs://resources/address_format/', $definitionPath);

        return $addressFormatRepository;
    }

    /**
     * @covers ::get
     * @covers ::loadDefinition
     * @covers ::createAddressFormatFromDefinition
     *
     * @uses \CommerceGuys\Addressing\Repository\DefinitionTranslatorTrait
     * @uses \CommerceGuys\Addressing\Model\AddressFormat
     * @uses \CommerceGuys\Addressing\Model\FormatStringTrait
     * @depends testConstructor
     */
    public function testGet($addressFormatRepository)
    {
        $expectedRequiredFields = [
            AddressField::ADDRESS_LINE1,
            AddressField::LOCALITY,
            AddressField::ADMINISTRATIVE_AREA,
            AddressField::POSTAL_CODE,
        ];
        $expectedUppercaseFields = [
            AddressField::LOCALITY,
            AddressField::ADMINISTRATIVE_AREA,
        ];
        $expectedFormat = $this->addressFormatES['format'];

        $addressFormat = $addressFormatRepository->get('ES');
        $this->assertInstanceOf('CommerceGuys\Addressing\Model\AddressFormat', $addressFormat);
        $this->assertEquals('ES', $addressFormat->getCountryCode());
        $this->assertEquals($expectedRequiredFields, $addressFormat->getRequiredFields());
        $this->assertEquals($expectedUppercaseFields, $addressFormat->getUppercaseFields());
        $this->assertEquals($expectedFormat, $addressFormat->getFormat());
        $this->assertEquals(AdministrativeAreaType::PROVINCE, $addressFormat->getAdministrativeAreaType());
        $this->assertEquals(LocalityType::CITY, $addressFormat->getLocalityType());
        $this->assertEquals(DependentLocalityType::SUBURB, $addressFormat->getDependentLocalityType());
        $this->assertEquals(PostalCodeType::POSTAL, $addressFormat->getPostalCodeType());
        $this->assertEquals('\\d{5}', $addressFormat->getPostalCodePattern());
        $this->assertEquals('A', $addressFormat->getPostalCodePrefix());
        $this->assertEquals('es', $addressFormat->getLocale());
    }

    /**
     * @covers ::get
     * @covers ::loadDefinition
     * @covers ::createAddressFormatFromDefinition
     *
     * @uses \CommerceGuys\Addressing\Repository\DefinitionTranslatorTrait
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
     * @covers ::createAddressFormatFromDefinition
     *
     * @uses \CommerceGuys\Addressing\Repository\AddressFormatRepository::get
     * @uses \CommerceGuys\Addressing\Repository\DefinitionTranslatorTrait
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
