<?php

namespace CommerceGuys\Addressing\Tests\Formatter;

use CommerceGuys\Addressing\Model\Address;
use CommerceGuys\Addressing\Formatter\PostalFormatter;
use CommerceGuys\Addressing\Provider\DataProvider;
use CommerceGuys\Addressing\Provider\DataProviderInterface;

/**
 * @coversDefaultClass \CommerceGuys\Addressing\Formatter\PostalFormatter
 */
class PostalFormatterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * The data provider.
     *
     * @var DataProviderInterface
     */
    protected $dataProvider;

    /**
     * The postal formatter.
     *
     * @var PostalFormatter
     */
    protected $postalFormatter;

    /**
     * The addresses.
     *
     * @var array
     */
    protected $addresses = array(
        'us' => array(
            'countryCode' => 'US',
            'administrativeArea' => 'US-CA',
            'locality' => 'Mountain View',
            'addressLine1' => '1234 Somewhere',
            'postalCode' => '94025'
        ),
        'cn' => array(
            'countryCode' => 'CN',
            'administrativeArea' => 'CN-11',
            'locality' => 'CN-11-30524e',
            'addressLine1' => 'Yitiao Lu',
            'postalCode' => '123456'

        ),
    );

    /**
     * {@inheritdoc}
     */
    public function setUp() {
        $this->dataProvider = new DataProvider();
        $this->postalFormatter = new PostalFormatter($this->dataProvider);
    }

    /**
     * @covers ::__construct
     * @uses CommerceGuys\Addressing\Provider\DataProvider
     */
    public function testConstructor() {
        $this->dataProvider = new DataProvider();
        $postalFormatter = new PostalFormatter($this->dataProvider);

        $this->assertInstanceOf('CommerceGuys\\Addressing\\Formatter\\PostalFormatter', $postalFormatter);
    }

    /**
     * @covers ::__construct
     * @covers ::format
     * @uses CommerceGuys\Addressing\Model\Address
     * @uses CommerceGuys\Addressing\Model\AddressFormat
     * @uses CommerceGuys\Addressing\Model\Subdivision
     * @uses CommerceGuys\Addressing\Provider\DataProvider
     *
     * @dataProvider formatterAddressProvider
     */
    public function testFormat(Address $address, $expectedLines, $originCountryCode, $originLocale = 'en') {
        $formattedAddress = $this->postalFormatter->format($address, $originCountryCode, $originLocale);

        $this->assertEquals($expectedLines, $formattedAddress);
    }

    /**
     * Provides the values for the format test.
     */
    public function formatterAddressProvider() {
        return array(
          array(
            $this->createAddress($this->addresses['us']),
            implode("\n", array('1234 Somewhere', 'MOUNTAIN VIEW, CA 94025')), 'US'
          ),
          array(
            $this->createAddress($this->addresses['us']),
            implode("\n", array('1234 Somewhere', 'MOUNTAIN VIEW, CA 94025', 'UNITED STATES')), 'FR'
          ),
          array(
            $this->createAddress($this->addresses['us']),
            implode("\n", array('1234 Somewhere', 'MOUNTAIN VIEW, CA 94025', 'ÉTATS-UNIS')), 'FR', 'fr'
          ),
          array(
            $this->createAddress($this->addresses['cn']),
            implode("\n", array('Yitiao Lu', '西城区', '北京市, 123456')), 'CN'
          ),
        );
    }

    /**
     * Helper function to create a address.
     *
     * @param array $values
     * @return Address
     */
    protected function createAddress(array $values) {
        $address = new Address();
        foreach ($values as $fieldName => $value) {
            $setter = 'set' . ucfirst($fieldName);
            $address->$setter($value);
        }

        return $address;
    }
}
