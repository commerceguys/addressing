<?php

namespace CommerceGuys\Addressing\Tests;

use CommerceGuys\Addressing\PostalCodeHelper;

/**
 * @coversDefaultClass \CommerceGuys\Addressing\PostalCodeHelper
 */
class PostalCodeHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::match
     * @covers ::matchRule
     * @covers ::buildList
     */
    public function testMatch()
    {
        // Empty rules should pass.
        $this->assertEquals(true, PostalCodeHelper::match('123', null, null));

        $includeRule = '/(20)[0-9]{1}/';
        $excludeRule = '/(20)[0-2]{1}/';
        $this->assertEquals(true, PostalCodeHelper::match('203', $includeRule, $excludeRule));
        $this->assertEquals(false, PostalCodeHelper::match('202', $includeRule, $excludeRule));

        $includeRule = '10, 20, 30:40';
        $excludeRule = '35';
        $this->assertEquals(true, PostalCodeHelper::match('34', $includeRule, $excludeRule));
        $this->assertEquals(false, PostalCodeHelper::match('35', $includeRule, $excludeRule));
    }
}
