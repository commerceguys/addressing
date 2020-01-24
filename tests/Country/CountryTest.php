<?php

namespace CommerceGuys\Addressing\Tests\Country;

use CommerceGuys\Addressing\Country\Country;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \CommerceGuys\Addressing\Country\Country
 */
final class CountryTest extends TestCase
{
    /**
     * @covers ::__construct
     */
    public function testMissingProperty()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required property "country_code".');
        $country = new Country([]);
    }

    /**
     * @covers ::__construct
     * @covers ::__toString
     * @covers ::getCountryCode
     * @covers ::getName
     * @covers ::getThreeLetterCode
     * @covers ::getNumericCode
     * @covers ::getCurrencyCode
     * @covers ::getTimezones
     * @covers ::getLocale
     */
    public function testValid()
    {
        $definition = [
            'country_code' => 'DE',
            'name' => 'Allemagne',
            'three_letter_code' => 'DEU',
            'numeric_code' => '276',
            'currency_code' => 'EUR',
            'locale' => 'fr',
        ];
        $country = new Country($definition);

        $this->assertEquals($definition['country_code'], $country->__toString());
        $this->assertEquals($definition['country_code'], $country->getCountryCode());
        $this->assertEquals($definition['name'], $country->getName());
        $this->assertEquals($definition['three_letter_code'], $country->getThreeLetterCode());
        $this->assertEquals($definition['numeric_code'], $country->getNumericCode());
        $this->assertEquals($definition['currency_code'], $country->getCurrencyCode());
        $this->assertEquals(['Europe/Berlin', 'Europe/Busingen'], $country->getTimezones());
        $this->assertEquals($definition['locale'], $country->getLocale());
    }
}
