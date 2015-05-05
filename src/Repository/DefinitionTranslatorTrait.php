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
        // Use the default locale if none was provided.
        $locale = $locale ?: $this->getDefaultLocale();
        if (is_null($locale) || empty($definition['translations'])) {
            // No locale or no translations found.
            return $definition;
        }

        // Normalize the locale. Allows en_US to work the same as en-US, etc.
        $locale = str_replace('_', '-', $locale);
        $localeVariants = $this->getLocaleVariants($locale);
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

    /**
     * Gets the defaut locale.
     *
     * @return string The default locale.
     */
    protected function getDefaultLocale()
    {
        return null;
    }

    /**
     * Gets all variants of a locale.
     *
     * For example, "bs-Cyrl-BA" has the following variants:
     * 1) bs-Cyrl-BA
     * 2) bs-Cyrl
     * 3) bs
     *
     * @todo Remove this method once the symfony/intl dependency is introduced.
     *
     * @param string $locale The locale (i.e. fr-FR).
     *
     * @return array An array of all variants of a locale.
     */
    protected function getLocaleVariants($locale)
    {
        $localeVariants = [];
        $localeParts = explode('-', $locale);
        while (!empty($localeParts)) {
            $localeVariants[] = implode('-', $localeParts);
            array_pop($localeParts);
        }

        return $localeVariants;
    }
}
