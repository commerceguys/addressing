<?php

namespace CommerceGuys\Addressing\Repository;

use CommerceGuys\Addressing\Model\Subdivision;

class SubdivisionRepository implements SubdivisionRepositoryInterface
{
    /**
     * The path where subdivision definitions are stored.
     *
     * @var string
     */
    protected $definitionPath;

    /**
     * Subdivision definitions.
     *
     * @var array
     */
    protected $definitions = array();

    /**
     * Creates a SubdivisionRepository instance.
     *
     * @param string $definitionPath Path to the subdivision definitions.
     *                               Defaults to 'resources/subdivision/'.
     */
    public function __construct($definitionPath = null)
    {
        $this->definitionPath = $definitionPath ?: __DIR__ . '/../../resources/subdivision/';
    }

    /**
     * {@inheritdoc}
     */
    public function get($id, $locale = null)
    {
        $idParts = explode('-', $id);
        if (count($idParts) < 2) {
            // Invalid id, nothing to load.
            return null;
        }

        // The default ids are constructed to contain the country code
        // and parent id. For "BR-AL-64b095" BR is the country code and BR-AL
        // is the parent id.
        array_pop($idParts);
        $countryCode = $idParts[0];
        $parentId = implode('-', $idParts);
        if ($parentId == $countryCode) {
            $parentId = 0;
        }
        $definitions = $this->loadDefinitions($countryCode, $parentId, $locale);
        if (!isset($definitions[$id])) {
            // No definition found.
            return null;
        }
        $definition = $this->translateDefinition($definitions[$id], $locale);

        return $this->createSubdivisionFromDefinition($definition);
    }

    /**
     * {@inheritdoc}
     */
    public function getAll($countryCode, $parentId = 0, $locale = null)
    {
        $definitions = $this->loadDefinitions($countryCode, $parentId, $locale);
        $subdivisions = array();
        foreach ($definitions as $id => $definition) {
            $definition = $this->translateDefinition($definition, $locale);
            $subdivisions[$id] = $this->createSubdivisionFromDefinition($definition);
        }

        return $subdivisions;
    }

    /**
     * Loads the subdivision definitions for the provided country code.
     *
     * @param string  $countryCode The country code.
     * @param integer $parentId    The parent id.
     *
     * @return array The subdivision definitions.
     */
    protected function loadDefinitions($countryCode, $parentId = 0)
    {
        if (!isset($this->definitions[$countryCode][$parentId])) {
            $filename = ($parentId === 0) ? $countryCode . '.json' : $parentId . '.json';
            $rawDefinition = @file_get_contents($this->definitionPath . $filename);
            if ($rawDefinition) {
                $this->definitions[$countryCode][$parentId] = json_decode($rawDefinition, true);
            } else {
                // Bypass further loading attempts.
                $this->definitions[$countryCode][$parentId] = array();
            }
        }

        return $this->definitions[$countryCode][$parentId];
    }

    /**
     * Translates the provided definition to the specified locale.
     *
     * If the provided definition doesn't have a translation for the
     * requested locale or one of its variants, the original definition
     * is returned unchanged.
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
        }
        // Apply the translation.
        $definition = $translation + $definition;

        return $definition;
    }

    /**
     * Creates a subdivision object from the provided definition.
     *
     * @param array $definition The subdivision definition.
     *
     * @return Subdivision
     */
    protected function createSubdivisionFromDefinition(array $definition)
    {
        $subdivision = new Subdivision();
        $subdivision->setCountryCode($definition['country_code']);
        $subdivision->setId($definition['id']);
        $subdivision->setCode($definition['code']);
        $subdivision->setName($definition['name']);
        $subdivision->setLocale($definition['locale']);
        if (isset($definition['postal_code_pattern'])) {
            $subdivision->setPostalCodePattern($definition['postal_code_pattern']);
        }
        if (isset($definition['parent_id'])) {
            // The full parent will be lazy-loaded by Subdivision::getParent().
            $parent = new Subdivision();
            $parent->setId($definition['parent_id']);
            $subdivision->setParent($parent);
        }
        if (!empty($definition['has_children'])) {
            // Signals that there are children and that they can be lazy-loaded.
            $subdivision->setChildren(array('load'));
        }

        return $subdivision;
    }
}
