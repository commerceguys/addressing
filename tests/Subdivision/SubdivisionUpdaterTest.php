<?php

namespace CommerceGuys\Addressing\Tests\Subdivision;

use CommerceGuys\Addressing\Subdivision\SubdivisionUpdater;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \CommerceGuys\Addressing\Subdivision\SubdivisionUpdater
 */
final class SubdivisionUpdaterTest extends TestCase
{
    /**
     * @covers ::updateValue
     * @covers ::getUpdateMap
     */
    public function testUpdateValue(): void
    {
        // No predefined subdivisions.
        $this->assertEquals('Test', SubdivisionUpdater::updateValue('RS', 'Test'));
        // Unknown value.
        $this->assertEquals('CA', SubdivisionUpdater::updateValue('JP', 'CA'));
        // Mapping.
        $this->assertEquals('01', SubdivisionUpdater::updateValue('JP', 'Hokkaido'));
    }
}
