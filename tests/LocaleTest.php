<?php

namespace CommerceGuys\Addressing\Tests;

use CommerceGuys\Addressing\Exception\UnknownLocaleException;
use CommerceGuys\Addressing\Locale;

/**
 * @coversDefaultClass \CommerceGuys\Addressing\Locale
 */
class LocaleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::match
     */
    public function testMatch()
    {
        $this->assertTrue(Locale::match('en-US', 'EN_us'));
        $this->assertTrue(Locale::match('de', 'de'));

        $this->assertFalse(Locale::match('de', 'de-AT'));
        $this->assertFalse(Locale::match('de', 'fr'));
    }

    /**
     * @covers ::matchCandidates
     */
    public function testMatchCandidates()
    {
        $this->assertTrue(Locale::matchCandidates('en-US', 'EN_us'));
        $this->assertTrue(Locale::matchCandidates('de', 'de'));
        $this->assertTrue(Locale::matchCandidates('de', 'de-AT'));

        $this->assertFalse(Locale::matchCandidates('de', 'fr'));
        // zh-Hant falls back to "root" instead of "zh".
        $this->assertFalse(Locale::matchCandidates('zh', 'zh-Hant'));
    }

    /**
     * @covers ::resolve
     */
    public function testResolve()
    {
        $availableLocales = ['bs-Cyrl', 'bs', 'en'];
        $locale = Locale::resolve($availableLocales, 'bs-Cyrl-BA');
        $this->assertEquals('bs-Cyrl', $locale);

        $locale = Locale::resolve($availableLocales, 'bs-Latn-BA');
        $this->assertEquals('bs', $locale);

        $locale = Locale::resolve($availableLocales, 'de', 'en');
        $this->assertEquals('en', $locale);
    }

    /**
     * @covers ::resolve
     */
    public function testResolveWithoutResult()
    {
        $this->setExpectedException(UnknownLocaleException::class);
        $availableLocales = ['bs', 'en'];
        $locale = Locale::resolve($availableLocales, 'de');
    }

    /**
     * @covers ::canonicalize
     */
    public function testCanonicalize()
    {
        $locale = Locale::canonicalize('BS_cyrl-ba');
        $this->assertEquals('bs-Cyrl-BA', $locale);

        $locale = Locale::canonicalize(null);
        $this->assertEquals(null, $locale);
    }

    /**
     * @covers ::getCandidates
     */
    public function testCandidates()
    {
        $candidates = Locale::getCandidates('en-US');
        $this->assertEquals(['en-US', 'en'], $candidates);

        $candidates = Locale::getCandidates('en-US', 'en');
        $this->assertEquals(['en-US', 'en'], $candidates);

        $candidates = Locale::getCandidates('sr', 'en-US');
        $this->assertEquals(['sr', 'en-US', 'en'], $candidates);

        $candidates = Locale::getCandidates('en-AU');
        $this->assertEquals(['en-AU', 'en-001', 'en'], $candidates);

        $candidates = Locale::getCandidates('sh');
        $this->assertEquals(['sr-Latn'], $candidates);
    }

    /**
     * @covers ::getParent
     */
    public function testParent()
    {
        $this->assertEquals('sr-Latn', Locale::getParent('sr-Latn-RS'));
        // sr-Latn falls back to "root" instead of "sr".
        $this->assertEquals(null, Locale::getParent('sr-Latn'));
        $this->assertEquals(null, Locale::getParent('sr'));
    }

    /**
     * @covers ::replaceAlias
     */
    public function testReplaceAlias()
    {
        $locale = Locale::replaceAlias('zh-CN');
        $this->assertEquals('zh-Hans-CN', $locale);

        $locale = Locale::replaceAlias(null);
        $this->assertEquals(null, $locale);
    }
}
