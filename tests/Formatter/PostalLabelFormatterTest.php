<?php

namespace CommerceGuys\Addressing\Tests\Formatter;

use CommerceGuys\Addressing\Address;
use CommerceGuys\Addressing\AddressFormat\AddressFormatRepository;
use CommerceGuys\Addressing\AddressFormat\AddressFormatRepositoryInterface;
use CommerceGuys\Addressing\Country\CountryRepository;
use CommerceGuys\Addressing\Country\CountryRepositoryInterface;
use CommerceGuys\Addressing\Formatter\PostalLabelFormatter;
use CommerceGuys\Addressing\Subdivision\SubdivisionRepository;
use CommerceGuys\Addressing\Subdivision\SubdivisionRepositoryInterface;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \CommerceGuys\Addressing\Formatter\PostalLabelFormatter
 */
final class PostalLabelFormatterTest extends TestCase
{
    protected AddressFormatRepositoryInterface $addressFormatRepository;

    protected CountryRepositoryInterface $countryRepository;

    protected SubdivisionRepositoryInterface $subdivisionRepository;

    protected PostalLabelFormatter $formatter;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->addressFormatRepository = new AddressFormatRepository();
        $this->countryRepository = new CountryRepository();
        $this->subdivisionRepository = new SubdivisionRepository();
        $this->formatter = new PostalLabelFormatter($this->addressFormatRepository, $this->countryRepository, $this->subdivisionRepository);
    }

    /**
     * @covers ::format
     */
    public function testMissingOriginCountryCode(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $address = new Address();
        $this->formatter->format($address);
    }

    /**
     * @covers \CommerceGuys\Addressing\Formatter\PostalLabelFormatter
     */
    public function testEmptyAddress(): void
    {
        $expectedLines = [];
        $formattedAddress = $this->formatter->format(new Address('US'), ['origin_country' => 'US']);
        $this->assertFormattedAddress($expectedLines, $formattedAddress);
    }

    /**
     * @covers \CommerceGuys\Addressing\Formatter\PostalLabelFormatter
     */
    public function testUnitedStatesAddress(): void
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
    public function testJapanAddressShippedFromFrance(): void
    {
        $address = new Address();
        $address = $address
            ->withCountryCode('JP')
            ->withAdministrativeArea('01')
            ->withLocality('Some City')
            ->withAddressLine1('Address Line 1')
            ->withAddressLine2('Address Line 2')
            ->withAddressLine3('Address Line 3')
            ->withPostalCode('04')
            ->withLocale('ja');

        // Test a JP address formatted for sending from France.
        $expectedLines = [
            'JAPON - JAPAN',
            '〒04',
            '北海道Some City',
            'Address Line 1',
            'Address Line 2',
            'Address Line 3',
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
    public function testAddressLeadingPostPrefix(): void
    {
        $address = new Address();
        $address = $address
            ->withCountryCode('HR')
            ->withLocality('Zagreb')
            ->withPostalCode('10105');

        // Domestic mail shouldn't have the postal code prefix added.
        $expectedLines = [
            '10105 ZAGREB',
        ];
        $formattedAddress = $this->formatter->format($address, ['origin_country' => 'HR']);
        $this->assertFormattedAddress($expectedLines, $formattedAddress);

        // International mail should have the postal code prefix added.
        $expectedLines = [
            'HR-10105 ZAGREB',
            'CROATIA',
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
    protected function assertFormattedAddress(array $expectedLines, string $formattedAddress): void
    {
        $expectedLines = implode("\n", $expectedLines);
        $this->assertEquals($expectedLines, $formattedAddress);
    }
}
