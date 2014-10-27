<?php

namespace CommerceGuys\Addressing\Tests\Repository;

/**
 * @coversDefaultClass \CommerceGuys\Addressing\Repository\DefinitionTranslatorTrait
 */
class DefinitionTranslatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DummyRepository
     */
    protected $repository;

    protected $definition = array(
        'locale' => 'en',
        'title' => 'English title',
        'description' => 'English description',
        'translations' => array(
            'fr' => array(
                'title' => 'French title',
                'description' => 'French description',
            ),
        ),
    );

    public function setUp()
    {
        $this->repository = new DummyRepository();
    }

    /**
     * @covers ::translateDefinition
     */
    public function testTranslation()
    {
        $definition = $this->repository->runTranslateDefinition($this->definition, 'fr');
        $expectedDefinition = array(
            'locale' => 'fr',
            'title' => 'French title',
            'description' => 'French description',
        );
        $this->assertEquals($expectedDefinition, $definition);
    }

    /**
     * @covers ::translateDefinition
     */
    public function testInvalidLocale()
    {
        $invalidLocales = array(null, 'de');
        foreach ($invalidLocales as $locale) {
            $definition = $this->repository->runTranslateDefinition($this->definition, $locale);
            $this->assertEquals($this->definition, $definition);
        }
    }
}
