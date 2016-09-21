<?php

namespace CommerceGuys\Addressing\Tests\Model;

use CommerceGuys\Addressing\Model\Address;

/**
 * @coversDefaultClass \CommerceGuys\Addressing\Model\Address
 */
class AddressTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     */
    public function testConstructor()
    {
        $address = new Address('US', 'US-CA', 'Mountain View', 'MV', '94043', '94044', '1600 Amphitheatre Parkway', 'Google Bldg 41', 'Google Inc.', 'John Smith', 'en');
        $this->assertEquals('US', $address->getCountryCode());
        $this->assertEquals('US-CA', $address->getAdministrativeArea());
        $this->assertEquals('Mountain View', $address->getLocality());
        $this->assertEquals('MV', $address->getDependentLocality());
        $this->assertEquals('94043', $address->getPostalCode());
        $this->assertEquals('94044', $address->getSortingCode());
        $this->assertEquals('1600 Amphitheatre Parkway', $address->getAddressLine1());
        $this->assertEquals('Google Bldg 41', $address->getAddressLine2());
        $this->assertEquals('Google Inc.', $address->getOrganization());
        $this->assertEquals('John Smith', $address->getRecipient());
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
        $address = (new Address())->withAdministrativeArea('US-CA');
        $this->assertEquals('US-CA', $address->getAdministrativeArea());
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
     * @covers ::getRecipient
     * @covers ::withRecipient
     */
    public function testRecipient()
    {
        $address = (new Address())->withRecipient('John Smith');
        $this->assertEquals('John Smith', $address->getRecipient());
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
