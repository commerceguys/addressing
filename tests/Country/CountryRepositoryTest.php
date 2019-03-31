<?php

namespace CommerceGuys\Addressing\Tests\Country;

use CommerceGuys\Addressing\Country\Country;
use CommerceGuys\Addressing\Country\CountryRepository;
use org\bovigo\vfs\vfsStream;

/**
 * @coversDefaultClass \CommerceGuys\Addressing\Country\CountryRepository
 */
class CountryRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Country definitions.
     *
     * @var array
     */
    protected $definitions = [
        'en' => [
            'FR' => 'France',
            'US' => 'United States',
        ],
        'es' => [
            'FR' => 'Francia',
            'US' => 'Estados Unidos',
        ],
        'de' => [
            'FR' => 'Frankreich',
            'US' => 'Vereinigte Staaten',
        ],
    ];

    /**
     * @covers ::__construct
     */
    public function testConstructor()
    {
        // Mock the existence of JSON definitions on the filesystem.
        $root = vfsStream::setup('resources');
        foreach ($this->definitions as $locale => $data) {
            vfsStream::newFile('country/' . $locale . '.json')->at($root)->setContent(json_encode($data));
        }

        // Instantiate the country repository and confirm that the definition path
        // was properly set.
        $countryRepository = new CountryRepository('de', 'en', 'vfs://resources/country/');
        $definitionPath = $this->getObjectAttribute($countryRepository, 'definitionPath');
        $this->assertEquals('vfs://resources/country/', $definitionPath);

        return $countryRepository;
    }

    /**
     * @covers ::get
     * @covers ::loadDefinitions
     *
     * @uses \CommerceGuys\Addressing\Country\Country
     * @uses \CommerceGuys\Addressing\Locale
     * @depends testConstructor
     */
    public function testGet($countryRepository)
    {
        // Explicit locale.
        $country = $countryRepository->get('FR', 'es');
        $this->assertInstanceOf(Country::class, $country);
        $this->assertEquals('FR', $country->getCountryCode());
        $this->assertEquals('Francia', $country->getName());
        $this->assertEquals('FRA', $country->getThreeLetterCode());
        $this->assertEquals('250', $country->getNumericCode());
        $this->assertEquals('EUR', $country->getCurrencyCode());
        $this->assertEquals('es', $country->getLocale());

        // Default locale, lowercase country code.
        $country = $countryRepository->get('fr');
        $this->assertInstanceOf(Country::class, $country);
        $this->assertEquals('FR', $country->getCountryCode());
        $this->assertEquals('Frankreich', $country->getName());
        $this->assertEquals('de', $country->getLocale());

        // Fallback locale.
        $country = $countryRepository->get('FR', 'INVALID-LOCALE');
        $this->assertInstanceOf(Country::class, $country);
        $this->assertEquals('FR', $country->getCountryCode());
        $this->assertEquals('France', $country->getName());
        $this->assertEquals('en', $country->getLocale());
    }

    /**
     * @covers ::get
     * @covers ::loadDefinitions
     *
     * @uses \CommerceGuys\Addressing\Locale
     * @expectedException \CommerceGuys\Addressing\Exception\UnknownCountryException
     * @depends testConstructor
     */
    public function testGetInvalidCountry($countryRepository)
    {
        $countryRepository->get('INVALID');
    }

    /**
     * @covers ::getAll
     * @covers ::loadDefinitions
     *
     * @uses \CommerceGuys\Addressing\Country\Country
     * @uses \CommerceGuys\Addressing\Locale
     * @depends testConstructor
     */
    public function testGetAll($countryRepository)
    {
        // Explicit locale.
        $countries = $countryRepository->getAll('es');
        $this->assertArrayHasKey('FR', $countries);
        $this->assertArrayHasKey('US', $countries);
        $this->assertEquals('Francia', $countries['FR']->getName());
        $this->assertEquals('Estados Unidos', $countries['US']->getName());

        // Default locale.
        $countries = $countryRepository->getAll();
        $this->assertArrayHasKey('FR', $countries);
        $this->assertArrayHasKey('US', $countries);
        $this->assertEquals('Frankreich', $countries['FR']->getName());
        $this->assertEquals('Vereinigte Staaten', $countries['US']->getName());

        // Fallback locale.
        $countries = $countryRepository->getAll('INVALID-LOCALE');
        $this->assertArrayHasKey('FR', $countries);
        $this->assertArrayHasKey('US', $countries);
        $this->assertEquals('France', $countries['FR']->getName());
        $this->assertEquals('United States', $countries['US']->getName());
    }

    /**
     * @covers ::getList
     * @covers ::loadDefinitions
     *
     * @uses \CommerceGuys\Addressing\Locale
     * @depends testConstructor
     */
    public function testGetList($countryRepository)
    {
        // Explicit locale.
        $list = $countryRepository->getList('es');
        $this->assertEquals(['FR' => 'Francia', 'US' => 'Estados Unidos'], $list);

        // Default locale.
        $list = $countryRepository->getList();
        $this->assertEquals(['FR' => 'Frankreich', 'US' => 'Vereinigte Staaten'], $list);

        // Fallback locale.
        $list = $countryRepository->getList('INVALID-LOCALE');
        $this->assertEquals(['FR' => 'France', 'US' => 'United States'], $list);
    }
}
