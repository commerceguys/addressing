<?php

namespace CommerceGuys\Addressing\Tests;

use CommerceGuys\Addressing\Subdivision\PatternType;

/**
 * @coversDefaultClass \CommerceGuys\Addressing\AbstractEnum
 */
class AbstractEnumTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::getAll
     */
    public function testGetAll()
    {
        $expectedValues = ['FULL' => 'full', 'START' => 'start'];
        $values = PatternType::getAll();
        $this->assertEquals($expectedValues, $values);
    }

    /**
     * @covers ::getKey
     */
    public function testGetKey()
    {
        $key = PatternType::getKey('full');
        $this->assertEquals('FULL', $key);

        $key = PatternType::getKey('invalid');
        $this->assertEquals(false, $key);
    }

    /**
     * @covers ::exists
     */
    public function testExists()
    {
        $result = PatternType::exists('start');
        $this->assertEquals(true, $result);

        $result = PatternType::exists('invalid');
        $this->assertEquals(false, $result);
    }

    /**
     * @covers ::assertExists
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage "invalid" is not a valid PatternType value.
     */
    public function testAssertExists()
    {
        $result = PatternType::assertExists('invalid');
    }

    /**
     * @covers ::assertAllExist
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage "invalid" is not a valid PatternType value.
     */
    public function testAssertAllExist()
    {
        $result = PatternType::assertAllExist(['start', 'invalid']);
    }
}
