<?php

namespace CommerceGuys\Addressing\Tests\Provider;

use CommerceGuys\Addressing\Provider\DataProvider;

/**
 * @coversDefaultClass \CommerceGuys\Addressing\Provider\DataProvider
 */
class DataProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Country definitions.
     *
     * @var array
     */
    protected $countryDefinitions = array(
        'FR' => array(
            'name' => 'France',
        ),
        'US' => array(
            'name' => 'United States',
        ),
    );

    /**
     * Address format definitions.
     *
     * @var array
     */
    protected $addressFormatDefinitions = array(
        'ES' => array(
            'format' => '%recipient\n%organization\n%address\n%postal_code %locality %administrative_area',
        ),
        'ZZ' => array(
            'format' => '%recipient\n%organization\n%address\n%locality',
        )
    );

    /**
     * Subdivision definitions.
     *
     * @var array
     */
    protected $subdivisionDefinitions = array(
        'BR-SC' => array(
            'country_code' => 'BR',
            'id' => 'BR-SC',
            'name' => 'Santa Catarina',
        ),
        'BR-SP' => array(
            'country_code' => 'BR',
            'id' => 'BR-SP',
            'name' => 'São Paulo',
        ),
    );

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->countryRepository = $this->getCountryRepository();
        $this->addressFormatRepository = $this->getAddressFormatRepository();
        $this->subdivisionRepository = $this->getSubdivisionRepository();
        $this->dataProvider = new DataProvider($this->countryRepository, $this->addressFormatRepository, $this->subdivisionRepository);
    }

    /**
     * @covers ::__construct
     */
    public function testConstructor()
    {
        // Note: other tests use $this->dataProvider instead of depending on
        // testConstructor because of a phpunit bug with dependencies and mocks:
        // https://github.com/sebastianbergmann/phpunit-mock-objects/issues/127
        $dataProvider = new DataProvider($this->countryRepository, $this->addressFormatRepository, $this->subdivisionRepository);
        $countryRepository = $this->getObjectAttribute($dataProvider, 'countryRepository');
        $addressFormatRepository = $this->getObjectAttribute($dataProvider, 'addressFormatRepository');
        $subdivisionRepository = $this->getObjectAttribute($dataProvider, 'subdivisionRepository');
        $this->assertEquals($this->countryRepository, $countryRepository);
        $this->assertEquals($this->addressFormatRepository, $addressFormatRepository);
        $this->assertEquals($this->subdivisionRepository, $subdivisionRepository);
    }

    /**
     * @covers ::getCountryName
     * @uses \CommerceGuys\Addressing\Provider\DataProvider::__construct
     */
    public function testGetCountryName()
    {
        $countryName = $this->dataProvider->getCountryName('FR');
        $this->assertEquals('France', $countryName);
    }

    /**
     * @covers ::getCountryNames
     * @uses \CommerceGuys\Addressing\Provider\DataProvider::__construct
     */
    public function testGetCountryNames()
    {
        $expectedNames = array();
        foreach ($this->countryDefinitions as $countryCode => $definition) {
            $expectedNames[$countryCode] = $definition['name'];
        }
        $countryNames = $this->dataProvider->getCountryNames();
        $this->assertEquals($expectedNames, $countryNames);
    }

    /**
     * @covers ::getAddressFormat
     * @uses \CommerceGuys\Addressing\Provider\DataProvider::__construct
     */
    public function testGetAddressFormat()
    {
        $addressFormat = $this->dataProvider->getAddressFormat('ES');
        $expectedAddressFormat = $this->addressFormatRepository->get('ES');
        $this->assertEquals($expectedAddressFormat, $addressFormat);
    }

    /**
     * @covers ::getAddressFormats
     * @uses \CommerceGuys\Addressing\Provider\DataProvider::__construct
     */
    public function testGetAddressFormats()
    {
        $addressFormats = $this->dataProvider->getAddressFormats();
        $expectedAddressFormats = $this->addressFormatRepository->getAll();
        $this->assertEquals($expectedAddressFormats, $addressFormats);
    }

    /**
     * @covers ::getSubdivision
     * @uses \CommerceGuys\Addressing\Provider\DataProvider::__construct
     */
    public function testGetSubdivision()
    {
        $subdivision = $this->dataProvider->getSubdivision('BR-SC');
        $expectedSubdivision = $this->subdivisionRepository->get('BR-SC');
        $this->assertEquals($expectedSubdivision, $subdivision);
    }

    /**
     * @covers ::getSubdivisions
     * @uses \CommerceGuys\Addressing\Provider\DataProvider::__construct
     */
    public function testGetSubdivisions()
    {
        $subdivisions = $this->dataProvider->getSubdivisions('BR');
        $expectedSubdivisions = $this->subdivisionRepository->getAll('BR');
        $this->assertEquals($expectedSubdivisions, $subdivisions);
    }

    /**
     * Returns a mock country repository.
     *
     * @return \CommerceGuys\Intl\Country\CountryRepository
     */
    protected function getCountryRepository()
    {
        // Create an array of mocks from the country definitions.
        $countries = array();
        foreach ($this->countryDefinitions as $countryCode => $definition) {
            $country = $this->getMock('CommerceGuys\Intl\Country\Country');
            $country
                ->expects($this->any())
                ->method('getCountryCode')
                ->will($this->returnValue($countryCode));
            $country
                ->expects($this->any())
                ->method('getName')
                ->will($this->returnValue($definition['name']));
            $countries[$countryCode] = $country;
        }

        // Mock the country repository
        $countryRepository = $this
            ->getMockBuilder('CommerceGuys\Intl\Country\CountryRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $countryRepository
            ->expects($this->any())
            ->method('get')
            ->with('FR')
            ->will($this->returnValue($countries['FR']));
        $countryRepository
            ->expects($this->any())
            ->method('getAll')
            ->will($this->returnValue($countries));

        return $countryRepository;
    }

    /**
     * Returns a mock address format repository.
     *
     * @return \CommerceGuys\Addressing\Repository\AddressFormatRepository
     */
    protected function getAddressFormatRepository()
    {
        // Create an array of mocks from the address format definitions.
        $addressFormats = array();
        foreach ($this->addressFormatDefinitions as $countryCode => $definition) {
            $addressFormat = $this->getMock('CommerceGuys\Addressing\Model\AddressFormat');
            $addressFormat
                ->expects($this->any())
                ->method('getCountryCode')
                ->will($this->returnValue($countryCode));
            $addressFormat
                ->expects($this->any())
                ->method('getFormat')
                ->will($this->returnValue($definition['format']));
            $addressFormats[$countryCode] = $addressFormat;
        }

        // Mock the address format repository
        $addressFormatRepository = $this
            ->getMockBuilder('CommerceGuys\Addressing\Repository\AddressFormatRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $addressFormatRepository
            ->expects($this->any())
            ->method('get')
            ->with('ES')
            ->will($this->returnValue($addressFormats['ES']));
        $addressFormatRepository
            ->expects($this->any())
            ->method('getAll')
            ->will($this->returnValue($addressFormats));

        return $addressFormatRepository;
    }

    /**
     * Returns a mock subdivision repository.
     *
     * @return \CommerceGuys\Addressing\Repository\SubdivisionRepository
     */
    protected function getSubdivisionRepository()
    {
        // Create an array of mocks from the subdivision definitions.
        $subdivisions = array();
        foreach ($this->subdivisionDefinitions as $id => $definition) {
            $subdivision = $this->getMock('CommerceGuys\Addressing\Model\Subdivision');
            $subdivision
                ->expects($this->any())
                ->method('getCountryCode')
                ->will($this->returnValue($definition['country_code']));
            $subdivision
                ->expects($this->any())
                ->method('getId')
                ->will($this->returnValue($definition['id']));
            $subdivision
                ->expects($this->any())
                ->method('getName')
                ->will($this->returnValue($definition['name']));
            $subdivisions[$id] = $subdivision;
        }

        // Mock the subdivision repository
        $subdivisionRepository = $this
            ->getMockBuilder('CommerceGuys\Addressing\Repository\SubdivisionRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $subdivisionRepository
            ->expects($this->any())
            ->method('get')
            ->with('BR-SC')
            ->will($this->returnValue($subdivisions['BR-SC']));
        $subdivisionRepository
            ->expects($this->any())
            ->method('getAll')
            ->with('BR')
            ->will($this->returnValue($subdivisions));

        return $subdivisionRepository;
    }
}
