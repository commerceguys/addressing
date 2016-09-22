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
     * @expectedException \RuntimeException
     */
    public function testMissingOriginCountryCode()
    {
        $address = new Address();
        $this->formatter->format($address);
    }

    /**
     * @covers ::getOriginCountryCode
     * @covers ::setOriginCountryCode
     */
    public function testOriginCountryCode()
    {
        $this->formatter->setOriginCountryCode('FR');
        $this->assertEquals('FR', $this->formatter->getOriginCountryCode('FR'));
    }

    /**
     * @covers \CommerceGuys\Addressing\Formatter\PostalLabelFormatter
     */
    public function testEmptyAddress()
    {
        $expectedLines = [];
        $this->formatter->setOriginCountryCode('US');
        $formattedAddress = $this->formatter->format(new Address('US'), 'US');
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
        $this->formatter->setOriginCountryCode('US');
        $formattedAddress = $this->formatter->format($address);
        $this->assertFormattedAddress($expectedLines, $formattedAddress);

        // Test a US address formatted for sending from France.
        $expectedLines = [
            '1098 Alta Ave',
            'MT VIEW, CA 94043',
            'ÉTATS-UNIS - UNITED STATES',
        ];
        $this->formatter->setOriginCountryCode('FR');
        $this->formatter->setLocale('fr');
        $formattedAddress = $this->formatter->format($address, 'FR', 'fr');
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
            'Address Line 2',
            'Address Line 1',
        ];
        $this->formatter->setOriginCountryCode('FR');
        $this->formatter->setLocale('fr');
        $formattedAddress = $this->formatter->format($address);
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
        $this->formatter->setOriginCountryCode('CH');
        $formattedAddress = $this->formatter->format($address);
        $this->assertFormattedAddress($expectedLines, $formattedAddress);

        // International mail should have the postal code prefix added.
        $expectedLines = [
            'CH-8047 Herrliberg',
            'SWITZERLAND',
        ];
        $this->formatter->setOriginCountryCode('FR');
        $formattedAddress = $this->formatter->format($address);
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
