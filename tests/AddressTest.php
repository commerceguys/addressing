<?php

namespace CommerceGuys\Addressing\Tests;

use CommerceGuys\Addressing\Address;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \CommerceGuys\Addressing\Address
 */
final class AddressTest extends TestCase
{
    /**
     * @covers ::__construct
     */
    public function testConstructor()
    {
        $address = new Address('US', 'CA', 'Mountain View', 'MV', '94043', '94044', '1600 Amphitheatre Parkway', 'Google Bldg 41', 'Google Inc.', 'John', '', 'Smith', 'en');
        $this->assertEquals('US', $address->getCountryCode());
        $this->assertEquals('CA', $address->getAdministrativeArea());
        $this->assertEquals('Mountain View', $address->getLocality());
        $this->assertEquals('MV', $address->getDependentLocality());
        $this->assertEquals('94043', $address->getPostalCode());
        $this->assertEquals('94044', $address->getSortingCode());
        $this->assertEquals('1600 Amphitheatre Parkway', $address->getAddressLine1());
        $this->assertEquals('Google Bldg 41', $address->getAddressLine2());
        $this->assertEquals('Google Inc.', $address->getOrganization());
        $this->assertEquals('John', $address->getGivenName());
        $this->assertEquals('Smith', $address->getFamilyName());
        $this->assertEquals('en', $address->getLocale());
    }

    /**
     * @covers ::getCountryCode
     * @covers ::withCountryCode
     */
    public function testCountryCode()
    {
        $address = (new Address())->withCountryCode('US');
        $this->assertEquals('US', $address->getCountryCode());
    }

    /**
     * @covers ::getAdministrativeArea
     * @covers ::withAdministrativeArea
     */
    public function testAdministrativeArea()
    {
        $address = (new Address())->withAdministrativeArea('CA');
        $this->assertEquals('CA', $address->getAdministrativeArea());
    }

    /**
     * @covers ::getLocality
     * @covers ::withLocality
     */
    public function testLocality()
    {
        $address = (new Address())->withLocality('Mountain View');
        $this->assertEquals('Mountain View', $address->getLocality());
    }

    /**
     * @covers ::getDependentLocality
     * @covers ::withDependentLocality
     */
    public function testDependentLocality()
    {
        // US doesn't use dependent localities, so there's no good example here.
        $address = (new Address())->withDependentLocality('Mountain View');
        $this->assertEquals('Mountain View', $address->getDependentLocality());
    }

    /**
     * @covers ::getPostalCode
     * @covers ::withPostalCode
     */
    public function testPostalCode()
    {
        $address = (new Address())->withPostalCode('94043');
        $this->assertEquals('94043', $address->getPostalCode());
    }

    /**
     * @covers ::getSortingCode
     * @covers ::withSortingCode
     */
    public function testSortingCode()
    {
        // US doesn't use sorting codes, so there's no good example here.
        $address = (new Address())->withSortingCode('94043');
        $this->assertEquals('94043', $address->getSortingCode());
    }

    /**
     * @covers ::getAddressLine1
     * @covers ::withAddressLine1
     */
    public function testAddressLine1()
    {
        $address = (new Address())->withAddressLine1('1600 Amphitheatre Parkway');
        $this->assertEquals('1600 Amphitheatre Parkway', $address->getAddressLine1());
    }

    /**
     * @covers ::getAddressLine2
     * @covers ::withAddressLine2
     */
    public function testAddressLine2()
    {
        $address = (new Address())->withAddressLine2('Google Bldg 41');
        $this->assertEquals('Google Bldg 41', $address->getAddressLine2());
    }

    /**
     * @covers ::getOrganization
     * @covers ::withOrganization
     */
    public function testOrganization()
    {
        $address = (new Address())->withOrganization('Google Inc.');
        $this->assertEquals('Google Inc.', $address->getOrganization());
    }

    /**
     * @covers ::getGivenName
     * @covers ::withGivenName
     */
    public function testGivenName()
    {
        $address = (new Address())->withGivenName('John');
        $this->assertEquals('John', $address->getGivenName());
    }

    /**
     * @covers ::getAdditionalName
     * @covers ::withAdditionalName
     */
    public function testAdditionalName()
    {
        $address = (new Address())->withAdditionalName('L.');
        $this->assertEquals('L.', $address->getAdditionalName());
    }

    /**
     * @covers ::getFamilyName
     * @covers ::withFamilyName
     */
    public function testFamilyName()
    {
        $address = (new Address())->withFamilyName('Smith');
        $this->assertEquals('Smith', $address->getFamilyName());
    }

    /**
     * @covers ::getLocale
     * @covers ::withLocale
     */
    public function testLocale()
    {
        $address = (new Address())->withLocale('en');
        $this->assertEquals('en', $address->getLocale());
    }
}
