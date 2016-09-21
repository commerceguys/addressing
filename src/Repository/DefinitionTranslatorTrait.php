<?php

namespace CommerceGuys\Addressing\Repository;

use CommerceGuys\Addressing\Helper\LocaleHelper;

trait DefinitionTranslatorTrait
{
    /**
     * Translates the provided definition to the specified locale.
     *
     * If the provided definition doesn't have a translation for the requested
     * locale or one of its variants, the definition is returned unchanged.
     *
     * @param array  $definition The definition.
     * @param string $locale     The locale.
     *
     * @return array The translated definition.
     */
    protected function translateDefinition(array $definition, $locale = null)
    {
        if (is_null($locale) || empty($definition['translations'])) {
            // No locale or no translations found.
            return $definition;
        }

        // Normalize the locale. Allows en_US to work the same as en-US, etc.
        $locale = str_replace('_', '-', $locale);
        $localeVariants = LocaleHelper::getVariants($locale);
        $translation = [];
        // Try to find a translation for one of the locale variants.
        foreach ($localeVariants as $localeVariant) {
            if (isset($definition['translations'][$localeVariant])) {
                $translation = $definition['translations'][$localeVariant];
                $definition['locale'] = $localeVariant;
                unset($definition['translations']);
                break;
            }
        }
        // Apply the translation.
        $definition = $translation + $definition;

        return $definition;
    }
}
