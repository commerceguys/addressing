<?php

namespace CommerceGuys\Addressing\Repository;

use CommerceGuys\Addressing\Collection\LazySubdivisionCollection;
use CommerceGuys\Addressing\Enum\PatternType;
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
     * Subdivision depths.
     *
     * @var array
     */
    protected $depths = [];

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

        return $this->createSubdivisionFromDefinitions($id, $definitions, $locale);
    }

    /**
     * {@inheritdoc}
     */
    public function getAll($countryCode, $parentId = null, $locale = null)
    {
        $definitions = $this->loadDefinitions($countryCode, $parentId);
        if (empty($definitions)) {
            return [];
        }

        $subdivisions = [];
        foreach (array_keys($definitions['subdivisions']) as $id) {
            $subdivisions[$id] = $this->createSubdivisionFromDefinitions($id, $definitions, $locale);
        }

        return $subdivisions;
    }

    /**
     * {@inheritdoc}
     */
    public function getList($countryCode, $parentId = null, $locale = null)
    {
        $definitions = $this->loadDefinitions($countryCode, $parentId);
        if (empty($definitions)) {
            return [];
        }

        $list = [];
        foreach ($definitions['subdivisions'] as $id => $definition) {
            $definition = $this->translateDefinition($definition, $locale);
            $list[$id] = $definition['name'];
        }

        return $list;
    }

    /**
     * {@inheritdoc}
     */
    public function getDepth($countryCode)
    {
        if (empty($this->depths)) {
            $filename = $this->definitionPath . 'depths.json';
            $this->depths = json_decode(file_get_contents($filename), true);
        }

        return isset($this->depths[$countryCode]) ? $this->depths[$countryCode] : 0;
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
        $lookupId = $parentId ? $parentId : $countryCode;
        if (isset($this->definitions[$lookupId])) {
            return $this->definitions[$lookupId];
        }

        // If there are predefined subdivisions at this level, try to load them.
        $this->definitions[$lookupId] = [];
        if ($this->hasData($countryCode, $parentId)) {
            $filename = $this->definitionPath . $lookupId . '.json';
            if ($rawDefinition = @file_get_contents($filename)) {
                $this->definitions[$lookupId] = json_decode($rawDefinition, true);
            }
        }

        return $this->definitions[$lookupId];
    }

    /**
     * Checks whether predefined subdivisions exist for the provided parent id.
     *
     * @param string $countryCode The country code.
     * @param int    $parentId    The parent id.
     *
     * @return bool TRUE if predefined subdivisions exist for the provided
     *              parent id, FALSE otherwise.
     */
    protected function hasData($countryCode, $parentId = null)
    {
        $depth = $this->getDepth($countryCode);
        if ($depth == 0) {
            return false;
        }

        // At least the first level has data.
        $hasData = true;
        if (!is_null($parentId)) {
            // After the first level it is possible for predefined subdivisions
            // to exist at a given level, but not for that specific parent.
            // That's why the parent definition has the most precise answer.
            $idParts = explode('-', $parentId);
            array_pop($idParts);
            $grandparentId = implode('-', $idParts);
            if (isset($this->definitions[$grandparentId]['subdivisions'][$parentId])) {
                $definition = $this->definitions[$grandparentId]['subdivisions'][$parentId];
                $hasData = !empty($definition['has_children']);
            } else {
                // The parent definition wasn't loaded previously, fallback
                // to guessing based on depth.
                $requestedDepth = substr_count($parentId, '-') + 1;
                $hasData = ($requestedDepth <= $depth);
            }
        }

        return $hasData;
    }

    /**
     * Creates a subdivision object from the provided definitions.
     *
     * @param int    $id         The subdivision id.
     * @param array  $definition The subdivision definitions.
     * @param string $locale     The locale (e.g. fr-FR).
     *
     * @return Subdivision
     */
    protected function createSubdivisionFromDefinitions($id, array $definitions, $locale)
    {
        if (!isset($definitions['subdivisions'][$id])) {
            // No matching definition found.
            return null;
        }

        $definition = $this->translateDefinition($definitions['subdivisions'][$id], $locale);
        // Add common keys from the root level.
        $definition['country_code'] = $definitions['country_code'];
        $definition['parent_id'] = $definitions['parent_id'];
        $definition['locale'] = $definitions['locale'];
        // Provide defaults.
        if (!isset($definition['code'])) {
            $definition['code'] = $definition['name'];
        }
        // Load the parent, if known.
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
        $setValues = \Closure::bind(function ($id, $definition) {
            $this->parent = $definition['parent'];
            $this->countryCode = $definition['country_code'];
            $this->id = $id;
            $this->code = $definition['code'];
            $this->name = $definition['name'];
            $this->locale = $definition['locale'];
            if (isset($definition['postal_code_pattern'])) {
                $this->postalCodePattern = $definition['postal_code_pattern'];
                if (isset($definition['postal_code_pattern_type'])) {
                    $this->postalCodePatternType = $definition['postal_code_pattern_type'];
                } else {
                    $this->postalCodePatternType = PatternType::getDefault();
                }
            }
        }, $subdivision, '\CommerceGuys\Addressing\Model\Subdivision');
        $setValues($id, $definition);

        if (!empty($definition['has_children'])) {
            $children = new LazySubdivisionCollection($definition['country_code'], $id, $definition['locale']);
            $children->setRepository($this);
            $subdivision->setChildren($children);
        }

        return $subdivision;
    }
}
