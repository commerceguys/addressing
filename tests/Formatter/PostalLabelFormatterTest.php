<?php

namespace CommerceGuys\Addressing\Tests\Formatter;

use CommerceGuys\Addressing\Address;
use CommerceGuys\Addressing\AddressFormat\AddressFormatRepository;
use CommerceGuys\Addressing\Country\CountryRepository;
use CommerceGuys\Addressing\Formatter\PostalLabelFormatter;
use CommerceGuys\Addressing\Subdivision\SubdivisionRepository;

/**
 * @coversDefaultClass \CommerceGuys\Addressing\Formatter\PostalLabelFormatter
 */
class PostalLabelFormatterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * The address format repository.
     *
     * @var AddressFormatRepositoryInterface
     */
    protected $addressFormatRepository;

    /**
     * The country repository.
     *
     * @var CountryRepositoryInterface
     */
    protected $countryRepository;

    /**
     * The subdivision repository.
     *
     * @var SubdivisionRepositoryInterface
     */
    protected $subdivisionRepository;

    /**
     * The formatter.
     *
     * @var PostalLabelFormatter
     */
    protected $formatter;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->addressFormatRepository = new AddressFormatRepository();
        $this->countryRepository = new CountryRepository();
        $this->subdivisionRepository = new SubdivisionRepository();
        $this->formatter = new PostalLabelFormatter($this->addressFormatRepository, $this->countryRepository, $this->subdivisionRepository);
    }

    /**
     * @covers ::format
     *
     * @expectedException \InvalidArgumentException
     */
    public function testMissingOriginCountryCode()
    {
        $address = new Address();
        $this->formatter->format($address);
    }

    /**
     * @covers \CommerceGuys\Addressing\Formatter\PostalLabelFormatter
     */
    public function testEmptyAddress()
    {
        $expectedLines = [];
        $formattedAddress = $this->formatter->format(new Address('US'), ['origin_country' => 'US']);
        $this->assertFormattedAddress($expectedLines, $formattedAddress);
    }

    /**
     * @covers \CommerceGuys\Addressing\Formatter\PostalLabelFormatter
     */
    public function testUnitedStatesAddress()
    {
        $address = new Address();
        $address = $address
            ->withCountryCode('US')
            ->withAdministrativeArea('CA')
            ->withLocality('Mt View')
            ->withPostalCode('94043')
            ->withAddressLine1('1098 Alta Ave');

        // Test a US address formatted for sending from the US.
        $expectedLines = [
            '1098 Alta Ave',
            'MT VIEW, CA 94043',
        ];
        $formattedAddress = $this->formatter->format($address, ['origin_country' => 'US']);
        $this->assertFormattedAddress($expectedLines, $formattedAddress);

        // Test a US address formatted for sending from France.
        $expectedLines = [
            '1098 Alta Ave',
            'MT VIEW, CA 94043',
            'ÉTATS-UNIS - UNITED STATES',
        ];
        $formattedAddress = $this->formatter->format($address, [
            'locale' => 'fr',
            'origin_country' => 'FR',
        ]);
        $this->assertFormattedAddress($expectedLines, $formattedAddress);
    }

    /**
     * @covers \CommerceGuys\Addressing\Formatter\PostalLabelFormatter
     */
    public function testJapanAddressShippedFromFrance()
    {
        $address = new Address();
        $address = $address
            ->withCountryCode('JP')
            ->withAdministrativeArea('Hokkaido')
            ->withLocality('Some City')
            ->withAddressLine1('Address Line 1')
            ->withAddressLine2('Address Line 2')
            ->withPostalCode('04')
            ->withLocale('ja');

        // Test a JP address formatted for sending from France.
        $expectedLines = [
            'JAPON - JAPAN',
            '〒04',
            '北海道Some City',
            'Address Line 1',
            'Address Line 2',
        ];
        $formattedAddress = $this->formatter->format($address, [
            'locale' => 'fr',
            'origin_country' => 'FR',
        ]);
        $this->assertFormattedAddress($expectedLines, $formattedAddress);
    }

    /**
     * @covers \CommerceGuys\Addressing\Formatter\PostalLabelFormatter
     */
    public function testAddressLeadingPostPrefix()
    {
        $address = new Address();
        $address = $address
            ->withCountryCode('CH')
            ->withLocality('Herrliberg')
            ->withPostalCode('8047');

        // Domestic mail shouldn't have the postal code prefix added.
        $expectedLines = [
            '8047 Herrliberg',
        ];
        $formattedAddress = $this->formatter->format($address, ['origin_country' => 'CH']);
        $this->assertFormattedAddress($expectedLines, $formattedAddress);

        // International mail should have the postal code prefix added.
        $expectedLines = [
            'CH-8047 Herrliberg',
            'SWITZERLAND',
        ];
        $formattedAddress = $this->formatter->format($address, ['origin_country' => 'FR']);
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
