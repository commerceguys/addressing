<?php

namespace CommerceGuys\Addressing\Tests\Formatter;

use CommerceGuys\Addressing\Model\Address;
use CommerceGuys\Addressing\Formatter\PostalFormatter;
use CommerceGuys\Addressing\Provider\DataProvider;

/**
 * @coversDefaultClass \CommerceGuys\Addressing\Formatter\PostalFormatter
 */
class PostalFormatterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * The data provider.
     *
     * @var DataProvider
     */
    protected $dataProvider;

    /**
     * The postal formatter.
     *
     * @var PostalFormatter
     */
    protected $postalFormatter;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->dataProvider = new DataProvider();
        $this->postalFormatter = new PostalFormatter($this->dataProvider);
    }

    /**
     * @covers ::__construct
     * @uses \CommerceGuys\Addressing\Provider\DataProvider
     * @uses \CommerceGuys\Addressing\Repository\AddressFormatRepository
     * @uses \CommerceGuys\Addressing\Repository\SubdivisionRepository
     */
    public function testConstructor()
    {
        $this->dataProvider = new DataProvider();
        $postalFormatter = new PostalFormatter($this->dataProvider);
        $this->assertEquals($this->dataProvider, $this->getObjectAttribute($postalFormatter, 'dataProvider'));
    }

    /**
     * @covers ::__construct
     * @covers ::format
     * @covers ::cleanupFormattedAddress
     * @uses \CommerceGuys\Addressing\Model\Address
     * @uses \CommerceGuys\Addressing\Model\AddressFormat
     * @uses \CommerceGuys\Addressing\Model\Subdivision
     * @uses \CommerceGuys\Addressing\Provider\DataProvider
     * @uses \CommerceGuys\Addressing\Repository\AddressFormatRepository
     * @uses \CommerceGuys\Addressing\Repository\SubdivisionRepository
     * @uses \CommerceGuys\Addressing\Repository\DefinitionTranslatorTrait
     */
    public function testUnitedStatesAddress()
    {
        $address = new Address();
        $address
            ->setCountryCode('US')
            ->setAdministrativeArea('US-CA')
            ->setLocality('Mt View')
            ->setAddressLine1('1098 Alta Ave')
            ->setPostalCode('94043');

        // Test a US address formatted for sending from the US.
        $expectedLines = [
            '1098 Alta Ave',
            'MT VIEW, CA 94043',
        ];
        $formattedAddress = $this->postalFormatter->format($address, 'US');
        $this->assertFormattedAddress($expectedLines, $formattedAddress);

        // Test a US address formatted for sending from France.
        $expectedLines = [
            '1098 Alta Ave',
            'MT VIEW, CA 94043',
            'ÉTATS-UNIS',
        ];
        $formattedAddress = $this->postalFormatter->format($address, 'FR', 'fr');
        $this->assertFormattedAddress($expectedLines, $formattedAddress);
    }

    /**
     * @covers ::__construct
     * @covers ::format
     * @covers ::cleanupFormattedAddress
     * @uses \CommerceGuys\Addressing\Collection\LazySubdivisionCollection
     * @uses \CommerceGuys\Addressing\Model\Address
     * @uses \CommerceGuys\Addressing\Model\AddressFormat
     * @uses \CommerceGuys\Addressing\Model\Subdivision
     * @uses \CommerceGuys\Addressing\Provider\DataProvider
     * @uses \CommerceGuys\Addressing\Repository\AddressFormatRepository
     * @uses \CommerceGuys\Addressing\Repository\SubdivisionRepository
     * @uses \CommerceGuys\Addressing\Repository\DefinitionTranslatorTrait
     */
    public function testTaiwanAddress()
    {
        // Real addresses in the major-to-minor order would be completely in
        // Traditional Chinese. That's not the case here, for readability.
        $expectedLines = [
            '106',
            '台北市大安區',
            'Sec. 3 Hsin-yi Rd.',
            'Giant Bike Store',
            'Mr. Liu',
        ];
        $address = new Address();
        $address
            ->setCountryCode('TW')
            ->setAdministrativeArea('TW-TPE')  // Taipei city
            ->setLocality('TW-TPE-e3cc33')  // Da-an district
            ->setAddressLine1('Sec. 3 Hsin-yi Rd.')
            ->setPostalCode('106')
            ->setOrganization('Giant Bike Store')
            ->setRecipient('Mr. Liu');

        $formattedAddress = $this->postalFormatter->format($address, 'TW', 'zh-hant');
        $this->assertFormattedAddress($expectedLines, $formattedAddress);
    }

    /**
     * @covers ::__construct
     * @covers ::format
     * @covers ::cleanupFormattedAddress
     * @uses \CommerceGuys\Addressing\Model\Address
     * @uses \CommerceGuys\Addressing\Model\AddressFormat
     * @uses \CommerceGuys\Addressing\Model\Subdivision
     * @uses \CommerceGuys\Addressing\Provider\DataProvider
     * @uses \CommerceGuys\Addressing\Repository\AddressFormatRepository
     * @uses \CommerceGuys\Addressing\Repository\SubdivisionRepository
     * @uses \CommerceGuys\Addressing\Repository\DefinitionTranslatorTrait
     */
    public function testElSalvadorAddress()
    {
        $expectedLines = [
            'Some Street 12',
            'AHUACHAPÁN',
            'AHUACHAPÁN',
        ];
        $address = new Address();
        $address
            ->setCountryCode('SV')
            ->setAdministrativeArea('Ahuachapán')
            ->setLocality('Ahuachapán')
            ->setAddressLine1('Some Street 12');

        $formattedAddress = $this->postalFormatter->format($address, 'SV');
        $this->assertFormattedAddress($expectedLines, $formattedAddress);

        $address->setPostalCode('CP 2101');
        $expectedLines = [
            'Some Street 12',
            'CP 2101-AHUACHAPÁN',
            'AHUACHAPÁN',
        ];

        $formattedAddress = $this->postalFormatter->format($address, 'SV');
        $this->assertFormattedAddress($expectedLines, $formattedAddress);
    }

    /**
     * @covers ::__construct
     * @covers ::format
     * @covers ::cleanupFormattedAddress
     * @uses \CommerceGuys\Addressing\Model\Address
     * @uses \CommerceGuys\Addressing\Model\AddressFormat
     * @uses \CommerceGuys\Addressing\Model\Subdivision
     * @uses \CommerceGuys\Addressing\Provider\DataProvider
     * @uses \CommerceGuys\Addressing\Repository\AddressFormatRepository
     * @uses \CommerceGuys\Addressing\Repository\SubdivisionRepository
     * @uses \CommerceGuys\Addressing\Repository\DefinitionTranslatorTrait
     */
    public function testIncompleteAddress()
    {
        $expectedLines = [
            '1098 Alta Ave',
            'CA 94043',
        ];
        // Create a US address without a locality.
        $address = new Address();
        $address
            ->setAdministrativeArea('US-CA')
            ->setCountryCode('US')
            ->setAddressLine1('1098 Alta Ave')
            ->setPostalCode('94043');

        $formattedAddress = $this->postalFormatter->format($address, 'US');
        $this->assertFormattedAddress($expectedLines, $formattedAddress);
    }

    /**
     * @covers ::__construct
     * @covers ::format
     * @covers ::cleanupFormattedAddress
     * @uses \CommerceGuys\Addressing\Model\Address
     * @uses \CommerceGuys\Addressing\Model\AddressFormat
     * @uses \CommerceGuys\Addressing\Model\Subdivision
     * @uses \CommerceGuys\Addressing\Provider\DataProvider
     * @uses \CommerceGuys\Addressing\Repository\AddressFormatRepository
     * @uses \CommerceGuys\Addressing\Repository\SubdivisionRepository
     * @uses \CommerceGuys\Addressing\Repository\DefinitionTranslatorTrait
     */
    public function testEmptyAddress()
    {
        $expectedLines = [];
        $address = new Address();
        $address->setCountryCode('US');

        $formattedAddress = $this->postalFormatter->format($address, 'US');
        $this->assertFormattedAddress($expectedLines, $formattedAddress);
    }

    /**
     * @covers ::__construct
     * @covers ::format
     * @covers ::cleanupFormattedAddress
     * @uses \CommerceGuys\Addressing\Model\Address
     * @uses \CommerceGuys\Addressing\Model\AddressFormat
     * @uses \CommerceGuys\Addressing\Model\Subdivision
     * @uses \CommerceGuys\Addressing\Provider\DataProvider
     * @uses \CommerceGuys\Addressing\Repository\AddressFormatRepository
     * @uses \CommerceGuys\Addressing\Repository\SubdivisionRepository
     * @uses \CommerceGuys\Addressing\Repository\DefinitionTranslatorTrait
     */
    public function testAddressLeadingPostPrefix()
    {
        $address = new Address();
        $address
            ->setCountryCode('CH')
            ->setLocality('Herrliberg')
            ->setPostalCode('8047');

        // Domestic mail shouldn't have the postal code prefix added.
        $expectedLines = [
            '8047 Herrliberg',
        ];
        $formattedAddress = $this->postalFormatter->format($address, 'CH');
        $this->assertFormattedAddress($expectedLines, $formattedAddress);

        // International mail should have the postal code prefix added.
        $expectedLines = [
            'CH-8047 Herrliberg',
            'SWITZERLAND',
        ];
        $formattedAddress = $this->postalFormatter->format($address, 'FR');
        $this->assertFormattedAddress($expectedLines, $formattedAddress);
    }

    /**
     * Asserts that the formatted address is valid.
     *
     * @param array  $expectedLines
     * @param string $formattedAddress
     */
    protected function assertFormattedAddress(array $expectedLines, $formattedAddress)
    {
        $expectedLines = implode("\n", $expectedLines);
        $this->assertEquals($expectedLines, $formattedAddress);
    }
}
