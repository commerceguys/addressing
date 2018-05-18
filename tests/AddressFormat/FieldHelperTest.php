<?php

namespace CommerceGuys\Addressing\Tests\AddressFormat;

use CommerceGuys\Addressing\AddressFormat\AddressField;
use CommerceGuys\Addressing\AddressFormat\FieldHelper;

/**
 * @coversDefaultClass \CommerceGuys\Addressing\AddressFormat\FieldHelper
 */
class FieldHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::getGroupedFields
     */
    public function testGetGroupedFields()
    {
        $format = "%givenName %familyName\n%organization\n%addressLine1\n%addressLine2\n%locality, %postalCode";
        $expectedGroupedFields = [
            [AddressField::GIVEN_NAME, AddressField::FAMILY_NAME],
            [AddressField::ORGANIZATION],
            [AddressField::ADDRESS_LINE1],
            [AddressField::ADDRESS_LINE2],
            [AddressField::LOCALITY, AddressField::POSTAL_CODE],
        ];
        $this->assertEquals($expectedGroupedFields, FieldHelper::getGroupedFields($format));
    }
}
