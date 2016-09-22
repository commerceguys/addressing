<?php

namespace CommerceGuys\Addressing\Tests;

use CommerceGuys\Addressing\LocaleHelper;

/**
 * @coversDefaultClass \CommerceGuys\Addressing\LocaleHelper
 */
class LocaleHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::match
     */
    public function testMatch()
    {
        $this->assertFalse(LocaleHelper::match('', 'pt'));
        $this->assertFalse(LocaleHelper::match('pt', ''));
        $this->assertFalse(LocaleHelper::match('pt', 'es'));
        $this->assertTrue(LocaleHelper::match('pt', 'pt'));
        $this->assertTrue(LocaleHelper::match('pt-BR', 'pt_BR'));
    }

    /**
     * @covers ::canonicalize
     */
    public function testCanonicalize()
    {
        $locale = LocaleHelper::canonicalize('BS_cyrl-ba');
        $this->assertEquals('bs-Cyrl-BA', $locale);
        $locale = LocaleHelper::canonicalize(null);
        $this->assertEquals(null, $locale);
    }

    /**
     * @covers ::getVariants
     */
    public function testGetVariants()
    {
        $variants = LocaleHelper::getVariants('bs-Cyrl-BA');
        $this->assertEquals(['bs-Cyrl-BA', 'bs-Cyrl', 'bs'], $variants);
    }
}
