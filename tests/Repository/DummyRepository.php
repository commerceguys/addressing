<?php

namespace CommerceGuys\Addressing\Tests\Repository;

use CommerceGuys\Addressing\Repository\DefinitionTranslatorTrait;

/**
 * Dummy repository used for testing the DefinitionTranslatorTrait.
 */
class DummyRepository
{
    use DefinitionTranslatorTrait;

    public function runTranslateDefinition($definition, $locale = null)
    {
        return $this->translateDefinition($definition, $locale);
    }
}
