<?php

namespace CommerceGuys\Addressing\Tests\Formatter;

use CommerceGuys\Addressing\Model\Address;
use CommerceGuys\Addressing\Formatter\PostalLabelFormatter;
use CommerceGuys\Addressing\Repository\AddressFormatRepository;
use CommerceGuys\Addressing\Repository\CountryRepository;
use CommerceGuys\Addressing\Repository\SubdivisionRepository;

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
     * @uses \CommerceGuys\Addressing\Formatter\PostalLabelFormatter::__construct
     * @uses \CommerceGuys\Addressing\Formatter\PostalLabelFormatter::getDefaultOptions
     * @uses \CommerceGuys\Addressing\Formatter\DefaultFormatter
     * @uses \CommerceGuys\Addressing\Model\Address
     * @uses \CommerceGuys\Addressing\Repository\AddressFormatRepository
     * @uses \CommerceGuys\Addressing\Repository\CountryRepository
     * @uses \CommerceGuys\Addressing\Repository\SubdivisionRepository
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
     *
     * @uses \CommerceGuys\Addressing\Formatter\PostalLabelFormatter::__construct
     * @uses \CommerceGuys\Addressing\Formatter\PostalLabelFormatter::getDefaultOptions
     * @uses \CommerceGuys\Addressing\Formatter\DefaultFormatter
     * @uses \CommerceGuys\Addressing\Repository\AddressFormatRepository
     * @uses \CommerceGuys\Addressing\Repository\CountryRepository
     * @uses \CommerceGuys\Addressing\Repository\SubdivisionRepository
     */
    public function testOriginCountryCode()
    {
        $this->formatter->setOriginCountryCode('FR');
        $this->assertEquals('FR', $this->formatter->getOriginCountryCode('FR'));
    }

    /**
     * @covers \CommerceGuys\Addressing\Formatter\PostalLabelFormatter
     *
     * @uses \CommerceGuys\Addressing\Formatter\DefaultFormatter
     * @uses \CommerceGuys\Addressing\Model\Address
     * @uses \CommerceGuys\Addressing\Model\AddressFormat
     * @uses \CommerceGuys\Addressing\Model\FormatStringTrait
     * @uses \CommerceGuys\Addressing\Model\Subdivision
     * @uses \CommerceGuys\Addressing\Repository\AddressFormatRepository
     * @uses \CommerceGuys\Addressing\Repository\CountryRepository
     * @uses \CommerceGuys\Addressing\Repository\SubdivisionRepository
     * @uses \CommerceGuys\Addressing\Repository\DefinitionTranslatorTrait
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
     *
     * @uses \CommerceGuys\Addressing\Formatter\DefaultFormatter
     * @uses \CommerceGuys\Addressing\Model\Address
     * @uses \CommerceGuys\Addressing\Model\AddressFormat
     * @uses \CommerceGuys\Addressing\Model\FormatStringTrait
     * @uses \CommerceGuys\Addressing\Model\Subdivision
     * @uses \CommerceGuys\Addressing\Repository\AddressFormatRepository
     * @uses \CommerceGuys\Addressing\Repository\CountryRepository
     * @uses \CommerceGuys\Addressing\Repository\SubdivisionRepository
     * @uses \CommerceGuys\Addressing\Repository\DefinitionTranslatorTrait
     */
    public function testUnitedStatesAddress()
    {
        $address = new Address();
        $address = $address
            ->withCountryCode('US')
            ->withAdministrativeArea('US-CA')
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
     *
     * @uses \CommerceGuys\Addressing\Formatter\DefaultFormatter
     * @uses \CommerceGuys\Addressing\Model\Address
     * @uses \CommerceGuys\Addressing\Model\AddressFormat
     * @uses \CommerceGuys\Addressing\Model\FormatStringTrait
     * @uses \CommerceGuys\Addressing\Model\Subdivision
     * @uses \CommerceGuys\Addressing\Repository\AddressFormatRepository
     * @uses \CommerceGuys\Addressing\Repository\CountryRepository
     * @uses \CommerceGuys\Addressing\Repository\SubdivisionRepository
     * @uses \CommerceGuys\Addressing\Repository\DefinitionTranslatorTrait
     */
    public function testJapanAddressShippedFromFrance()
    {
        $address = new Address();
        $address = $address
            ->withCountryCode('JP')
            ->withAdministrativeArea('JP-01')
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
     *
     * @uses \CommerceGuys\Addressing\Formatter\DefaultFormatter
     * @uses \CommerceGuys\Addressing\Model\Address
     * @uses \CommerceGuys\Addressing\Model\AddressFormat
     * @uses \CommerceGuys\Addressing\Model\FormatStringTrait
     * @uses \CommerceGuys\Addressing\Model\Subdivision
     * @uses \CommerceGuys\Addressing\Repository\AddressFormatRepository
     * @uses \CommerceGuys\Addressing\Repository\CountryRepository
     * @uses \CommerceGuys\Addressing\Repository\SubdivisionRepository
     * @uses \CommerceGuys\Addressing\Repository\DefinitionTranslatorTrait
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
