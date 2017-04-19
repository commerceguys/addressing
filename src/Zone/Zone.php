<?php

namespace CommerceGuys\Addressing\Zone;

use CommerceGuys\Addressing\AddressInterface;

/**
 * Represents a zone.
 */
class Zone
{
    /**
     * The ID.
     *
     * @var string
     */
    protected $id;

    /**
     * The label.
     *
     * @var string
     */
    protected $label;

    /**
     * The territories.
     *
     * @var \CommerceGuys\Addressing\Zone\ZoneTerritory[]
     */
    protected $territories;

    /**
     * Creates a new Zone instance.
     *
     * @param array $definition The definition array.
     */
    public function __construct(array $definition)
    {
        foreach (['id', 'label', 'territories'] as $required_property) {
            if (empty($definition[$required_property])) {
                throw new \InvalidArgumentException(sprintf('Missing required property "%s".', $required_property));
            }
        }
        if (!is_array($definition['territories'])) {
            throw new \InvalidArgumentException(sprintf('The property "territories" must be an array.'));
        }

        $this->id = $definition['id'];
        $this->label = $definition['label'];
        foreach ($definition['territories'] as $territory_definition) {
            $this->territories[] = new ZoneTerritory($territory_definition);
        }
    }

    /**
     * Gets the ID.
     *
     * @return string The ID.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Gets the label.
     *
     * @return string The label.
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Gets the territories.
     *
     * @return \CommerceGuys\Addressing\Zone\ZoneTerritory[] The territories.
     */
    public function getTerritories()
    {
        return $this->territories;
    }

    /**
     * Checks whether the provided address belongs to the zone.
     *
     * @param \CommerceGuys\Addressing\AddressInterface $address The address.
     *
     * @return bool True if the provided address belongs to the zone, false otherwise.
     */
    public function match(AddressInterface $address)
    {
        foreach ($this->territories as $territory) {
            if ($territory->match($address)) {
                return true;
            }
        }
        return false;
    }
}
