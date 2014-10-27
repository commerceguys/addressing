<?php

namespace CommerceGuys\Addressing\Repository;

use CommerceGuys\Addressing\Model\AddressFormat;

class AddressFormatRepository implements AddressFormatRepositoryInterface
{
    use DefinitionTranslatorTrait;

    /**
     * The path where address format definitions are stored.
     *
     * @var string
     */
    protected $definitionPath;

    /**
     * Address format definitions.
     *
     * @var array
     */
    protected $definitions = array();

    /**
     * Creates an AddressFormatRepository instance.
     *
     * @param string $definitionPath Path to the address format definitions.
     *                               Defaults to 'resources/address_format'.
     */
    public function __construct($definitionPath = null)
    {
        $this->definitionPath = $definitionPath ?: __DIR__ . '/../../resources/address_format/';
    }

    /**
     * {@inheritdoc}
     */
    public function get($countryCode, $locale = null)
    {
        $definition = $this->loadDefinition($countryCode);
        if (!$definition) {
            // No definition found for the given country code, fallback to ZZ.
            $definition = $this->loadDefinition('ZZ');
        }
        $definition = $this->translateDefinition($definition, $locale);

        return $this->createAddressFormatFromDefinition($definition);
    }

    /**
     * {@inheritdoc}
     */
    public function getAll($locale = null)
    {
        // Gather available address formats.
        // This is slow, but survivable because the only use case for
        // fetching all address formats is mass import into another storage.
        $addressFormats = array();
        if ($handle = opendir($this->definitionPath)) {
            while (false !== ($entry = readdir($handle))) {
                if (substr($entry, 0, 1) != '.') {
                    $countryCode = strtok($entry, '.');
                    $addressFormats[$countryCode] = $this->get($countryCode, $locale);
                }
            }
            closedir($handle);
        }

        return $addressFormats;
    }

    /**
     * Loads the address format definition for the provided country code.
     *
     * @param string $countryCode The country code.
     *
     * @return array The address format definition.
     */
    protected function loadDefinition($countryCode)
    {
        if (!isset($this->definitions[$countryCode])) {
            $filename = $this->definitionPath . $countryCode . '.json';
            $rawDefinition = @file_get_contents($filename);
            if ($rawDefinition) {
                $rawDefinition = json_decode($rawDefinition, true);
                $rawDefinition['country_code'] = $countryCode;
                $this->definitions[$countryCode] = $rawDefinition;
            } else {
                // Bypass further loading attempts.
                $this->definitions[$countryCode] = array();
            }
        }

        return $this->definitions[$countryCode];
    }

    /**
     * Creates an address format object from the provided definition.
     *
     * @param array $definition The address format definition.
     *
     * @return AddressFormat
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
}
