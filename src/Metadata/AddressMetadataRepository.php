<?php

namespace CommerceGuys\Addressing\Metadata;

use CommerceGuys\Intl\Country\CountryManagerInstance;
use CommerceGuys\Intl\Country\DefaultCountryManager;

class AddressMetadataRepository implements AddressMetadataRepositoryInterface
{
    /**
     * Address format definitions.
     *
     * @var array
     */
    protected $addressFormatDefinitions = array();

    /**
     * Subdivision definitions.
     *
     * @var array
     */
    protected $subdivisionDefinitions = array();

    /**
     * The country manager.
     *
     * @var CountryManagerInstance
     */
    protected $countryManager;

    /**
     * Creates an AddressMetadataManager instance.
     *
     * @param string $definitionPath The path to the metadata definitions.
     *                               Defaults to 'resources/'.
     */
    public function __construct($definitionPath = null, CountryManagerInstance $countryManager = null)
    {
        $this->definitionPath = $definitionPath ?: __DIR__ . '/../../resources/';
        $this->countryManager = $countryManager ?: new DefaultCountryManager();
    }

    /**
     * {@inheritdoc}
     */
    public function getCountryName($countryCode, $locale = null)
    {
        $country = $this->countryManager->get($countryCode, $locale);

        return $country->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function getCountryNames($locale = null)
    {
        $countries = $this->countryManager->getAll($locale);
        $countryNames = array();
        foreach ($countries as $countryCode => $country) {
            $countryName[$countryCode] = $country->getName();
        }

        return $countryNames;
    }

    /**
     * {@inheritdoc}
     */
    public function getAddressFormat($countryCode, $locale = null)
    {
        $definition = $this->loadAddressFormatDefinition($countryCode);
        if (!$definition) {
            // No definition found for the given country code, fallback to ZZ.
            $definition = $this->loadAddressFormatDefinition('ZZ');
        }
        $definition = $this->translateDefinition($definition, $locale);

        return $this->createAddressFormatFromDefinition($definition);
    }

    /**
     * {@inheritdoc}
     */
    public function getSubdivision($id, $locale = null)
    {
        $idParts = explode('-', $id);
        if (count($idParts) < 2) {
            throw new \InvalidArgumentException(sprintf('The provided id "%s" is invalid.', $id));
        }

        array_pop($idParts);
        $countryCode = $idParts[0];
        $parentId = implode('-', $idParts);
        if ($parentId == $countryCode) {
            $parentId = 0;
        }
        $definitions = $this->loadSubdivisionDefinitions($countryCode, $parentId, $locale);
        $definition = $this->translateDefinition($definitions[$id], $locale);

        return $this->createSubdivisionFromDefinition($definition);
    }

    /**
     * {@inheritdoc}
     */
    public function getSubdivisions($countryCode, $parentId = 0, $locale = null)
    {
        $definitions = $this->loadSubdivisionDefinitions($countryCode, $parentId, $locale);
        $subdivisions = array();
        foreach ($definitions as $id => $definition) {
            $definition = $this->translateDefinition($definition, $locale);
            $subdivisions[$id] = $this->createSubdivisionFromDefinition($definition);
        }

        return $subdivisions;
    }

    /**
     * Loads the address format definition for the provided country code.
     *
     * @param string $countryCode The country code.
     *
     * @return array The address format definition.
     */
    protected function loadAddressFormatDefinition($countryCode)
    {
        if (!isset($this->addressFormatDefinitions[$countryCode])) {
            $filename = $this->definitionPath . 'address_format/' . $countryCode . '.json';
            $rawDefinition = file_get_contents($filename);
            if ($rawDefinition) {
                $rawDefinition = json_decode($rawDefinition, true);
                $rawDefinition['country_code'] = $countryCode;
                $this->addressFormatDefinitions[$countryCode] = $rawDefinition;
            } else {
                // Bypass further loading attempts.
                $this->addressFormatDefinitions[$countryCode] = array();
            }
        }

        return $this->addressFormatDefinitions[$countryCode];
    }

    /**
     * Loads the subdivision definitions for the provided country code.
     *
     * @param string  $countryCode The country code.
     * @param integer $parentId    The parent id.
     *
     * @return array The subdivision definitions.
     */
    protected function loadSubdivisionDefinitions($countryCode, $parentId = 0)
    {
        if (!isset($this->subdivisionDefinitions[$countryCode][$parentId])) {
            $filename = ($parentId === 0) ? $countryCode . '.json' : $parentId . '.json';
            $rawDefinition = file_get_contents($this->definitionPath . 'subdivision/' . $filename);
            if (!$rawDefinition) {
                throw new \Exception(sprintf('The subdivision/%s definition file could not be found.', $filename));
            }
            $this->subdivisionDefinitions[$countryCode][$parentId] = json_decode($rawDefinition, true);
        }

        return $this->subdivisionDefinitions[$countryCode][$parentId];
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
     * Creates an address format object from the provided definition.
     *
     * @param array $definition The address format definition.
     *
     * @return \CommerceGuys\Addressing\Metadata\AddressFormat
     */
    protected function createAddressFormatFromDefinition(array $definition)
    {
        $addressFormat = new AddressFormat();
        $addressFormat->setCountryCode($definition['country_code']);
        $addressFormat->setFormat($definition['format']);
        $addressFormat->setRequiredFields($definition['required_fields']);
        $addressFormat->setUppercaseFields($definition['uppercase_fields']);
        $addressFormat->setAdministrativeAreaType($definition['administrative_area_type']);
        $addressFormat->setPostalCodeType($definition['postal_code_type']);
        $addressFormat->setLocale($definition['locale']);
        if (isset($definition['postal_code_pattern'])) {
            $addressFormat->setPostalCodePattern($definition['postal_code_pattern']);
        }
        if (isset($definition['postal_code_prefix'])) {
            $addressFormat->setPostalCodePrefix($definition['postal_code_prefix']);
        }

        return $addressFormat;
    }

    /**
     * Creates a subdivision object from the provided definition.
     *
     * @param array $definition The subdivision definition.
     *
     * @return \CommerceGuys\Addressing\Metadata\Subdivision
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
