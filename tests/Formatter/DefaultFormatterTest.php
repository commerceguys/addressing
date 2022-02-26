<?php

namespace CommerceGuys\Addressing\Tests\Formatter;

use CommerceGuys\Addressing\Address;
use CommerceGuys\Addressing\AddressFormat\AddressFormatRepository;
use CommerceGuys\Addressing\AddressFormat\AddressFormatRepositoryInterface;
use CommerceGuys\Addressing\Country\CountryRepository;
use CommerceGuys\Addressing\Country\CountryRepositoryInterface;
use CommerceGuys\Addressing\Formatter\DefaultFormatter;
use CommerceGuys\Addressing\Subdivision\SubdivisionRepository;
use CommerceGuys\Addressing\Subdivision\SubdivisionRepositoryInterface;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \CommerceGuys\Addressing\Formatter\DefaultFormatter
 */
final class DefaultFormatterTest extends TestCase
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
     * @var DefaultFormatter
     */
    protected $formatter;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->addressFormatRepository = new AddressFormatRepository();
        $this->countryRepository = new CountryRepository();
        $this->subdivisionRepository = new SubdivisionRepository();
        $this->formatter = new DefaultFormatter($this->addressFormatRepository, $this->countryRepository, $this->subdivisionRepository);
    }

    /**
     * @covers ::__construct
     */
    public function testConstructor(): void
    {
        $formatter = new DefaultFormatter($this->addressFormatRepository, $this->countryRepository, $this->subdivisionRepository);

        $reflected_constraint = (new \ReflectionObject($formatter))->getProperty('addressFormatRepository');
        $reflected_constraint->setAccessible(TRUE);
        $constraint = $reflected_constraint->getValue($formatter);
        $this->assertInstanceOf(AddressFormatRepository::class, $constraint);

        $reflected_constraint = (new \ReflectionObject($formatter))->getProperty('countryRepository');
        $reflected_constraint->setAccessible(TRUE);
        $constraint = $reflected_constraint->getValue($formatter);
        $this->assertInstanceOf(CountryRepository::class, $constraint);

        $reflected_constraint = (new \ReflectionObject($formatter))->getProperty('subdivisionRepository');
        $reflected_constraint->setAccessible(TRUE);
        $constraint = $reflected_constraint->getValue($formatter);
        $this->assertInstanceOf(SubdivisionRepository::class, $constraint);
    }

    /**
     * @covers ::__construct
     */
    public function testUnrecognizedOption()
    {
        $this->expectException(\InvalidArgumentException::class);
        $formatter = new DefaultFormatter($this->addressFormatRepository, $this->countryRepository, $this->subdivisionRepository, ['unrecognized' => '123']);
    }

    /**
     * @covers ::__construct
     */
    public function testInvalidOption()
    {
        $this->expectException(\InvalidArgumentException::class);
        $formatter = new DefaultFormatter($this->addressFormatRepository, $this->countryRepository, $this->subdivisionRepository, ['html' => 'INVALID']);
    }

    /**
     * @covers \CommerceGuys\Addressing\Formatter\DefaultFormatter
     */
    public function testAndorraAddress()
    {
        $address = new Address();
        $address = $address
            ->withCountryCode('AD')
            ->withLocality("Parròquia d'Andorra la Vella")
            ->withPostalCode('AD500')
            ->withAddressLine1('C. Prat de la Creu, 62-64');

        // Andorra has no predefined administrative areas, but it does have
        // predefined localities, which must be shown.
        $expectedTextLines = [
            'C. Prat de la Creu, 62-64',
            "AD500 Parròquia d'Andorra la Vella",
            'Andorra',
        ];
        $textAddress = $this->formatter->format($address, ['html' => false]);
        $this->assertFormattedAddress($expectedTextLines, $textAddress);
    }

    /**
     * @covers \CommerceGuys\Addressing\Formatter\DefaultFormatter
     */
    public function testElSalvadorAddress()
    {
        $address = new Address();
        $address = $address
            ->withCountryCode('SV')
            ->withAdministrativeArea('Ahuachapán')
            ->withLocality('Ahuachapán')
            ->withAddressLine1('Some Street 12');

        $expectedHtmlLines = [
            '<p translate="no">',
            '<span class="address-line1">Some Street 12</span><br>',
            '<span class="locality">Ahuachapán</span><br>',
            '<span class="administrative-area">Ahuachapán</span><br>',
            '<span class="country">El Salvador</span>',
            '</p>',
        ];
        $htmlAddress = $this->formatter->format($address);
        $this->assertFormattedAddress($expectedHtmlLines, $htmlAddress);

        $expectedTextLines = [
            'Some Street 12',
            'Ahuachapán',
            'Ahuachapán',
            'El Salvador',
        ];
        $textAddress = $this->formatter->format($address, ['html' => false]);
        $this->assertFormattedAddress($expectedTextLines, $textAddress);

        $address = $address->withPostalCode('CP 2101');
        $expectedHtmlLines = [
            '<p translate="no">',
            '<span class="address-line1">Some Street 12</span><br>',
            '<span class="postal-code">CP 2101</span>-<span class="locality">Ahuachapán</span><br>',
            '<span class="administrative-area">Ahuachapán</span><br>',
            '<span class="country">El Salvador</span>',
            '</p>',
        ];
        $htmlAddress = $this->formatter->format($address);
        $this->assertFormattedAddress($expectedHtmlLines, $htmlAddress);

        $expectedTextLines = [
            'Some Street 12',
            'CP 2101-Ahuachapán',
            'Ahuachapán',
            'El Salvador',
        ];
        $textAddress = $this->formatter->format($address, ['html' => false]);
        $this->assertFormattedAddress($expectedTextLines, $textAddress);
    }

    /**
     * @covers \CommerceGuys\Addressing\Formatter\DefaultFormatter
     */
    public function testTaiwanAddress()
    {
        // Real addresses in the major-to-minor order would be completely in
        // Traditional Chinese. That's not the case here, for readability.
        $address = new Address();
        $address = $address
            ->withCountryCode('TW')
            ->withAdministrativeArea('Taipei City')
            ->withLocality("Da'an District")
            ->withAddressLine1('Sec. 3 Hsin-yi Rd.')
            ->withPostalCode('106')
            // Any HTML in the fields is supposed to be removed when formatting
            // for text, and escaped when formatting for html.
            ->withOrganization('Giant <h2>Bike</h2> Store')
            ->withGivenName('Te-Chiang')
            ->withFamilyName('Liu')
            ->withLocale('zh-Hant');

        $expectedHtmlLines = [
            '<p translate="no" class="address postal-address">',
            '<span class="country">台灣</span><br>',
            '<span class="postal-code">106</span><br>',
            '<span class="administrative-area">台北市</span><span class="locality">大安區</span><br>',
            '<span class="address-line1">Sec. 3 Hsin-yi Rd.</span><br>',
            '<span class="organization">Giant &lt;h2&gt;Bike&lt;/h2&gt; Store</span><br>',
            '<span class="family-name">Liu</span> <span class="given-name">Te-Chiang</span>',
            '</p>',
        ];
        // Test wrapper attributes and a custom locale.
        $htmlAddress = $this->formatter->format($address, [
            'locale' => 'zh-Hant',
            'html_attributes' => [
                'translate' => 'no',
                'class' => ['address', 'postal-address'],
            ],
        ]);
        $this->assertFormattedAddress($expectedHtmlLines, $htmlAddress);

        $expectedTextLines = [
            '台灣',
            '106',
            '台北市大安區',
            'Sec. 3 Hsin-yi Rd.',
            'Giant Bike Store',
            'Liu Te-Chiang',
        ];
        $textAddress = $this->formatter->format($address, [
            'locale' => 'zh-Hant',
            'html' => false,
        ]);
        $this->assertFormattedAddress($expectedTextLines, $textAddress);
    }

    /**
     * @covers \CommerceGuys\Addressing\Formatter\DefaultFormatter
     */
    public function testUnitedStatesIncompleteAddress()
    {
        // Create a US address without a locality.
        $address = new Address();
        $address = $address
            ->withCountryCode('US')
            ->withAdministrativeArea('CA')
            ->withPostalCode('94043')
            ->withAddressLine1('1098 Alta Ave');

        $expectedHtmlLines = [
            '<p translate="no">',
            '<span class="address-line1">1098 Alta Ave</span><br>',
            '<span class="administrative-area">CA</span> <span class="postal-code">94043</span><br>',
            '<span class="country">United States</span>',
            '</p>',
        ];
        $htmlAddress = $this->formatter->format($address);
        $this->assertFormattedAddress($expectedHtmlLines, $htmlAddress);

        $expectedTextLines = [
            '1098 Alta Ave',
            'CA 94043',
            'United States',
        ];
        $textAddress = $this->formatter->format($address, ['html' => false]);
        $this->assertFormattedAddress($expectedTextLines, $textAddress);

        // Now add the locality, but remove the administrative area.
        $address = $address
            ->withLocality('Mountain View')
            ->withAdministrativeArea('');

        $expectedHtmlLines = [
            '<p translate="no">',
            '<span class="address-line1">1098 Alta Ave</span><br>',
            '<span class="locality">Mountain View</span>, <span class="postal-code">94043</span><br>',
            '<span class="country">United States</span>',
            '</p>',
        ];
        $htmlAddress = $this->formatter->format($address);
        $this->assertFormattedAddress($expectedHtmlLines, $htmlAddress);

        $expectedTextLines = [
            '1098 Alta Ave',
            'Mountain View, 94043',
            'United States',
        ];
        $textAddress = $this->formatter->format($address, ['html' => false]);
        $this->assertFormattedAddress($expectedTextLines, $textAddress);
    }

    /**
     * Asserts that the formatted address is valid.
     */
    protected function assertFormattedAddress(array $expectedLines, string $formattedAddress)
    {
        $expectedLines = implode("\n", $expectedLines);
        $this->assertEquals($expectedLines, $formattedAddress);
    }
}
