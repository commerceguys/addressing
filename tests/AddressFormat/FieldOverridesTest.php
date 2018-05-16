<?php

namespace CommerceGuys\Addressing\Tests\AddressFormat;

use CommerceGuys\Addressing\AddressFormat\AddressField;
use CommerceGuys\Addressing\AddressFormat\FieldOverride;
use CommerceGuys\Addressing\AddressFormat\FieldOverrides;

/**
 * @coversDefaultClass \CommerceGuys\Addressing\AddressFormat\FieldOverrides
 */
class FieldOverridesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     *
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidField()
    {
        $definition = [
            'INVALID_FIELD' => FieldOverride::HIDDEN,
        ];
        $fieldOverrides = new FieldOverrides($definition);
    }

    /**
     * @covers ::__construct
     *
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidOverride()
    {
        $definition = [
            AddressField::POSTAL_CODE => 'INVALID',
        ];
        $fieldOverrides = new FieldOverrides($definition);
    }

    /**
     * @covers ::__construct
     */
    public function testEmptyDefinition()
    {
        $fieldOverrides = new FieldOverrides([]);
        $this->assertSame([], $fieldOverrides->getHiddenFields());
        $this->assertSame([], $fieldOverrides->getOptionalFields());
        $this->assertSame([], $fieldOverrides->getRequiredFields());
    }

    /**
     * @covers ::__construct
     * @covers ::getHiddenFields
     * @covers ::getOptionalFields
     * @covers ::getRequiredFields
     */
    public function testOverrides()
    {
        $fieldOverrides = new FieldOverrides([
            AddressField::GIVEN_NAME => FieldOverride::HIDDEN,
            AddressField::ADDITIONAL_NAME => FieldOverride::HIDDEN,
            AddressField::FAMILY_NAME => FieldOverride::HIDDEN,
            AddressField::ORGANIZATION => FieldOverride::REQUIRED,
            AddressField::POSTAL_CODE => FieldOverride::OPTIONAL,
        ]);
        $this->assertSame([
            AddressField::GIVEN_NAME,
            AddressField::ADDITIONAL_NAME,
            AddressField::FAMILY_NAME
        ], $fieldOverrides->getHiddenFields());
        $this->assertSame([AddressField::POSTAL_CODE], $fieldOverrides->getOptionalFields());
        $this->assertSame([AddressField::ORGANIZATION], $fieldOverrides->getRequiredFields());
    }
}
