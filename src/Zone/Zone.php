<?php

namespace CommerceGuys\Addressing\Zone;

use CommerceGuys\Addressing\AddressInterface;

/**
 * Represents a zone.
 */
class Zone
{
    protected string $id;

    protected string $label;

    /**
     * @var ZoneTerritory[]
     */
    protected array $territories;

    public function __construct(array $definition)
    {
        foreach (['id', 'label', 'territories'] as $required_property) {
            if (empty($definition[$required_property])) {
                throw new \InvalidArgumentException(sprintf('Missing required property "%s".', $required_property));
            }
        }
        if (!is_array($definition['territories'])) {
            throw new \InvalidArgumentException('The property "territories" must be an array.');
        }

        $this->id = $definition['id'];
        $this->label = $definition['label'];
        foreach ($definition['territories'] as $territory_definition) {
            $this->territories[] = new ZoneTerritory($territory_definition);
        }
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * Gets the territories.
     *
     * @return ZoneTerritory[] The territories.
     */
    public function getTerritories(): array
    {
        return $this->territories;
    }

    /**
     * Checks whether the provided address belongs to the zone.
     */
    public function match(AddressInterface $address): bool
    {
        foreach ($this->territories as $territory) {
            if ($territory->match($address)) {
                return true;
            }
        }
        return false;
    }
}
