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
    protected $country;

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
        $this->assertEquals($this->address->getLocale(), 'en');
    }

    /**
     * @covers ::getCountryCode
     * @covers ::setCountryCode
     */
    public function testCountryCode()
    {
        $this->address->setCountryCode('US');
        $this->assertEquals($this->address->getCountryCode(), 'US');
    }

    /**
     * @covers ::getAdministrativeArea
     * @covers ::setAdministrativeArea
     */
    public function testAdministrativeArea()
    {
        $this->address->setAdministrativeArea('US-CA');
        $this->assertEquals($this->address->getAdministrativeArea(), 'US-CA');
    }

    /**
     * @covers ::getLocality
     * @covers ::setLocality
     */
    public function testLocality()
    {
        $this->address->setLocality('Mountain View');
        $this->assertEquals($this->address->getLocality(), 'Mountain View');
    }

    /**
     * @covers ::getDependentLocality
     * @covers ::setDependentLocality
     */
    public function testDependentLocality()
    {
        // US doesn't use dependent localities, so there's no good example here.
        $this->address->setDependentLocality('Mountain View');
        $this->assertEquals($this->address->getDependentLocality(), 'Mountain View');
    }

    /**
     * @covers ::getPostalCode
     * @covers ::setPostalCode
     */
    public function testPostalCode()
    {
        $this->address->setPostalCode('94043');
        $this->assertEquals($this->address->getPostalCode(), '94043');
    }

    /**
     * @covers ::getSortingCode
     * @covers ::setSortingCode
     */
    public function testSortingCode()
    {
        // US doesn't use sorting codes, so there's no good example here.
        $this->address->setSortingCode('94043');
        $this->assertEquals($this->address->getSortingCode(), '94043');
    }

    /**
     * @covers ::getAddressLine1
     * @covers ::setAddressLine1
     */
    public function testAddressLine1()
    {
        $this->address->setAddressLine1('1600 Amphitheatre Parkway');
        $this->assertEquals($this->address->getAddressLine1(), '1600 Amphitheatre Parkway');
    }

    /**
     * @covers ::getAddressLine2
     * @covers ::setAddressLine2
     */
    public function testAddressLine2()
    {
        $this->address->setAddressLine2('Google Bldg 41');
        $this->assertEquals($this->address->getAddressLine2(), 'Google Bldg 41');
    }

    /**
     * @covers ::getOrganization
     * @covers ::setOrganization
     */
    public function testOrganization()
    {
        $this->address->setOrganization('Google Inc.');
        $this->assertEquals($this->address->getOrganization(), 'Google Inc.');
    }

    /**
     * @covers ::getRecipient
     * @covers ::setRecipient
     */
    public function testRecipient()
    {
        $this->address->setRecipient('John Smith');
        $this->assertEquals($this->address->getRecipient(), 'John Smith');
    }
}
