<?php

namespace CommerceGuys\Addressing\Repository;

use CommerceGuys\Addressing\Collection\LazySubdivisionCollection;
use CommerceGuys\Addressing\Model\Subdivision;

class SubdivisionRepository implements SubdivisionRepositoryInterface
{
    use DefinitionTranslatorTrait;

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
    protected $definitions = [];

    /**
     * Parent subdivisions.
     *
     * Used as a cache to speed up instantiating subdivisions with the same
     * parent. Contains only parents instead of all instantiated subdivisions
     * to minimize duplicating the data in $this->definitions, thus reducing
     * memory usage.
     *
     * @var array
     */
    protected $parents = [];

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
            $parentId = null;
        }
        $definitions = $this->loadDefinitions($countryCode, $parentId);
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
    public function getAll($countryCode, $parentId = null, $locale = null)
    {
        $definitions = $this->loadDefinitions($countryCode, $parentId);
        $subdivisions = [];
        foreach ($definitions as $id => $definition) {
            $definition = $this->translateDefinition($definition, $locale);
            $subdivisions[$id] = $this->createSubdivisionFromDefinition($definition);
        }

        return $subdivisions;
    }

    /**
     * Loads the subdivision definitions for the provided country code.
     *
     * @param string $countryCode The country code.
     * @param int    $parentId    The parent id.
     *
     * @return array The subdivision definitions.
     */
    protected function loadDefinitions($countryCode, $parentId = null)
    {
        if (!isset($this->definitions[$countryCode][$parentId])) {
            $filename = $parentId ? $parentId . '.json' : $countryCode . '.json';
            $rawDefinition = @file_get_contents($this->definitionPath . $filename);
            if ($rawDefinition) {
                $this->definitions[$countryCode][$parentId] = json_decode($rawDefinition, true);
            } else {
                // Bypass further loading attempts.
                $this->definitions[$countryCode][$parentId] = [];
            }
        }

        return $this->definitions[$countryCode][$parentId];
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
        $definition['parent'] = null;
        if (isset($definition['parent_id'])) {
            $parentId = $definition['parent_id'];
            if (!isset($this->parents[$parentId])) {
                $this->parents[$parentId] = $this->get($definition['parent_id']);
            }
            $definition['parent'] = $this->parents[$parentId];
        }

        $subdivision = new Subdivision();
        // Bind the closure to the Subdivision object, giving it access to its
        // protected properties. Faster than both setters and reflection.
        $setValues = \Closure::bind(function ($definition) {
            $this->parent = $definition['parent'];
            $this->countryCode = $definition['country_code'];
            $this->id = $definition['id'];
            $this->code = $definition['code'];
            $this->name = $definition['name'];
            $this->locale = $definition['locale'];
            if (isset($definition['postal_code_pattern'])) {
                $this->postalCodePattern = $definition['postal_code_pattern'];
            }
        }, $subdivision, '\CommerceGuys\Addressing\Model\Subdivision');
        $setValues($definition);

        if (!empty($definition['has_children'])) {
            $children = new LazySubdivisionCollection($definition['country_code'], $definition['id'], $definition['locale']);
            $children->setRepository($this);
            $subdivision->setChildren($children);
        }

        return $subdivision;
    }
}
