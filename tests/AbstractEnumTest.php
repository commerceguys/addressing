<?php

namespace CommerceGuys\Addressing\Tests;

use CommerceGuys\Addressing\Subdivision\PatternType;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \CommerceGuys\Addressing\AbstractEnum
 */
final class AbstractEnumTest extends TestCase
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
     */
    public function testAssertExists()
    {
        $this->expectExceptionMessage("\"invalid\" is not a valid PatternType value.");
        $this->expectException(\InvalidArgumentException::class);
        $result = PatternType::assertExists('invalid');
    }

    /**
     * @covers ::assertAllExist
     */
    public function testAssertAllExist()
    {
        $this->expectExceptionMessage("\"invalid\" is not a valid PatternType value.");
        $this->expectException(\InvalidArgumentException::class);
        $result = PatternType::assertAllExist(['start', 'invalid']);
    }
}
