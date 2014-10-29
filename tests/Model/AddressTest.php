<?php

namespace CommerceGuys\Addressing\Tests\Model;

use CommerceGuys\Addressing\Model\Address;

/**
 * @coversDefaultClass \CommerceGuys\Addressing\Model\Address
 */
class AddressTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Address
     */
    protected $address;

    public function setUp()
    {
        $this->address = new Address();
    }

    /**
     * @covers ::getLocale
     * @covers ::setLocale
     */
    public function testLocale()
    {
        $this->address->setLocale('en');
        $this->assertEquals('en', $this->address->getLocale());
    }

    /**
     * @covers ::getCountryCode
     * @covers ::setCountryCode
     */
    public function testCountryCode()
    {
        $this->address->setCountryCode('US');
        $this->assertEquals('US', $this->address->getCountryCode());
    }

    /**
     * @covers ::getAdministrativeArea
     * @covers ::setAdministrativeArea
     */
    public function testAdministrativeArea()
    {
        $this->address->setAdministrativeArea('US-CA');
        $this->assertEquals('US-CA', $this->address->getAdministrativeArea());
    }

    /**
     * @covers ::getLocality
     * @covers ::setLocality
     */
    public function testLocality()
    {
        $this->address->setLocality('Mountain View');
        $this->assertEquals('Mountain View', $this->address->getLocality());
    }

    /**
     * @covers ::getDependentLocality
     * @covers ::setDependentLocality
     */
    public function testDependentLocality()
    {
        // US doesn't use dependent localities, so there's no good example here.
        $this->address->setDependentLocality('Mountain View');
        $this->assertEquals('Mountain View', $this->address->getDependentLocality());
    }

    /**
     * @covers ::getPostalCode
     * @covers ::setPostalCode
     */
    public function testPostalCode()
    {
        $this->address->setPostalCode('94043');
        $this->assertEquals('94043', $this->address->getPostalCode());
    }

    /**
     * @covers ::getSortingCode
     * @covers ::setSortingCode
     */
    public function testSortingCode()
    {
        // US doesn't use sorting codes, so there's no good example here.
        $this->address->setSortingCode('94043');
        $this->assertEquals('94043', $this->address->getSortingCode());
    }

    /**
     * @covers ::getAddressLine1
     * @covers ::setAddressLine1
     */
    public function testAddressLine1()
    {
        $this->address->setAddressLine1('1600 Amphitheatre Parkway');
        $this->assertEquals('1600 Amphitheatre Parkway', $this->address->getAddressLine1());
    }

    /**
     * @covers ::getAddressLine2
     * @covers ::setAddressLine2
     */
    public function testAddressLine2()
    {
        $this->address->setAddressLine2('Google Bldg 41');
        $this->assertEquals('Google Bldg 41', $this->address->getAddressLine2());
    }

    /**
     * @covers ::getOrganization
     * @covers ::setOrganization
     */
    public function testOrganization()
    {
        $this->address->setOrganization('Google Inc.');
        $this->assertEquals('Google Inc.', $this->address->getOrganization());
    }

    /**
     * @covers ::getRecipient
     * @covers ::setRecipient
     */
    public function testRecipient()
    {
        $this->address->setRecipient('John Smith');
        $this->assertEquals('John Smith', $this->address->getRecipient());
    }
}
