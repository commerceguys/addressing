<?php

namespace CommerceGuys\Addressing\Tests;

use CommerceGuys\Addressing\UpdateHelper;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \CommerceGuys\Addressing\UpdateHelper
 */
final class UpdateHelperTest extends TestCase
{
    /**
     * @covers ::splitRecipient
     */
    public function testSplitRecipient()
    {
        $expectedName = ['givenName' => 'Erzsébet', 'familyName' => 'Báthory'];
        $this->assertEquals($expectedName, UpdateHelper::splitRecipient('Báthory Erzsébet', 'HU'));
        $expectedName = ['givenName' => 'Matt', 'familyName' => 'Glaman'];
        $this->assertEquals($expectedName, UpdateHelper::splitRecipient('Matt Glaman', 'US'));
    }

    /**
     * @covers ::updateSubdivision
     * @covers ::loadSubdivisionUpdateMap
     */
    public function testUpdateSubdivision()
    {
        // No predefined subdivisions.
        $this->assertEquals('RS-RS', UpdateHelper::updateSubdivision('RS-RS'));
        // No dash.
        $this->assertEquals('California', UpdateHelper::updateSubdivision('California'));
        // Simple conversion.
        $this->assertEquals('CA', UpdateHelper::updateSubdivision('US-CA'));
        // Mapping.
        $this->assertEquals('Hokkaido', UpdateHelper::updateSubdivision('JP-01'));
        // Unknown.
        $this->assertEquals('JP-CA', UpdateHelper::updateSubdivision('JP-CA'));
    }
}
