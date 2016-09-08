<?php

namespace CommerceGuys\Addressing\Tests\Helper;

use CommerceGuys\Addressing\Helper\UpdateHelper;

/**
 * @coversDefaultClass \CommerceGuys\Addressing\Helper\UpdateHelper
 */
class UpdateHelperTest extends \PHPUnit_Framework_TestCase
{
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
