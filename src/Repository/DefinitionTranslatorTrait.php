<?php

namespace CommerceGuys\Addressing\Repository;

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
        if (is_null($locale)) {
            // No locale specified, nothing to do.
            return $definition;
        }

        // Normalize the locale. Allows en_US to work the same as en-US, etc.
        $locale = str_replace('_', '-', $locale);
        $translation = array();
        // Try to find a translation for the specified locale in the definition.
        if (isset($locale, $definition['translations'], $definition['translations'][$locale])) {
            $translation = $definition['translations'][$locale];
            $definition['locale'] = $locale;
            unset($definition['translations']);
        }
        // Apply the translation.
        $definition = $translation + $definition;

        return $definition;
    }
}
