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
     *
     * @uses \CommerceGuys\Addressing\Model\Address::getCountryCode
     * @uses \CommerceGuys\Addressing\Model\Address::getAdministrativeArea
     * @uses \CommerceGuys\Addressing\Model\Address::getLocality
     * @uses \CommerceGuys\Addressing\Model\Address::getDependentLocality
     * @uses \CommerceGuys\Addressing\Model\Address::getPostalCode
     * @uses \CommerceGuys\Addressing\Model\Address::getSortingCode
     * @uses \CommerceGuys\Addressing\Model\Address::getAddressLine1
     * @uses \CommerceGuys\Addressing\Model\Address::getAddressLine2
     * @uses \CommerceGuys\Addressing\Model\Address::getOrganization
     * @uses \CommerceGuys\Addressing\Model\Address::getRecipient
     * @uses \CommerceGuys\Addressing\Model\Address::getLocale
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
     *
     * @uses \CommerceGuys\Addressing\Model\Address::__construct
     */
    public function testCountryCode()
    {
        $address = (new Address())->withCountryCode('US');
        $this->assertEquals('US', $address->getCountryCode());
    }

    /**
     * @covers ::getAdministrativeArea
     * @covers ::withAdministrativeArea
     *
     * @uses \CommerceGuys\Addressing\Model\Address::__construct
     */
    public function testAdministrativeArea()
    {
        $address = (new Address())->withAdministrativeArea('US-CA');
        $this->assertEquals('US-CA', $address->getAdministrativeArea());
    }

    /**
     * @covers ::getLocality
     * @covers ::withLocality
     *
     * @uses \CommerceGuys\Addressing\Model\Address::__construct
     */
    public function testLocality()
    {
        $address = (new Address())->withLocality('Mountain View');
        $this->assertEquals('Mountain View', $address->getLocality());
    }

    /**
     * @covers ::getDependentLocality
     * @covers ::withDependentLocality
     *
     * @uses \CommerceGuys\Addressing\Model\Address::__construct
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
     *
     * @uses \CommerceGuys\Addressing\Model\Address::__construct
     */
    public function testPostalCode()
    {
        $address = (new Address())->withPostalCode('94043');
        $this->assertEquals('94043', $address->getPostalCode());
    }

    /**
     * @covers ::getSortingCode
     * @covers ::withSortingCode
     *
     * @uses \CommerceGuys\Addressing\Model\Address::__construct
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
     *
     * @uses \CommerceGuys\Addressing\Model\Address::__construct
     */
    public function testAddressLine1()
    {
        $address = (new Address())->withAddressLine1('1600 Amphitheatre Parkway');
        $this->assertEquals('1600 Amphitheatre Parkway', $address->getAddressLine1());
    }

    /**
     * @covers ::getAddressLine2
     * @covers ::withAddressLine2
     *
     * @uses \CommerceGuys\Addressing\Model\Address::__construct
     */
    public function testAddressLine2()
    {
        $address = (new Address())->withAddressLine2('Google Bldg 41');
        $this->assertEquals('Google Bldg 41', $address->getAddressLine2());
    }

    /**
     * @covers ::getOrganization
     * @covers ::withOrganization
     *
     * @uses \CommerceGuys\Addressing\Model\Address::__construct
     */
    public function testOrganization()
    {
        $address = (new Address())->withOrganization('Google Inc.');
        $this->assertEquals('Google Inc.', $address->getOrganization());
    }

    /**
     * @covers ::getRecipient
     * @covers ::withRecipient
     *
     * @uses \CommerceGuys\Addressing\Model\Address::__construct
     */
    public function testRecipient()
    {
        $address = (new Address())->withRecipient('John Smith');
        $this->assertEquals('John Smith', $address->getRecipient());
    }

    /**
     * @covers ::getLocale
     * @covers ::withLocale
     *
     * @uses \CommerceGuys\Addressing\Model\Address::__construct
     */
    public function testLocale()
    {
        $address = (new Address())->withLocale('en');
        $this->assertEquals('en', $address->getLocale());
    }
}
