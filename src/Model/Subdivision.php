<?php

namespace CommerceGuys\Addressing\Model;

use CommerceGuys\Addressing\Enum\PatternType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Default subdivision implementation.
 *
 * Can be mapped and used by Doctrine.
 * However, due to the high number of subdivisions (>12k) and their high update
 * rate, most implementing applications will want to continue loading them
 * from disk (with a possible caching layer in front), instead of importing
 * them into a database.
 */
class Subdivision implements SubdivisionEntityInterface
{
    /**
     * The parent.
     *
     * @var SubdivisionEntityInterface
     */
    protected $parent;

    /**
     * The country code.
     *
     * @var string
     */
    protected $countryCode;

    /**
     * The subdivision id.
     *
     * @var string
     */
    protected $id;

    /**
     * The subdivision code.
     *
     * @var string
     */
    protected $code;

    /**
     * The subdivision name.
     *
     * @var string
     */
    protected $name;

    /**
     * The postal code pattern.
     *
     * @var string
     */
    protected $postalCodePattern;

    /**
     * The postal code pattern type.
     *
     * @var string
     */
    protected $postalCodePatternType;

    /**
     * The children.
     *
     * @param SubdivisionEntityInterface[]
     */
    protected $children;

    /**
     * The locale.
     *
     * @var string
     */
    protected $locale;

    /**
     * Creates a Subdivision instance.
     */
    public function __construct()
    {
        $this->children = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * {@inheritdoc}
     */
    public function setParent(SubdivisionEntityInterface $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * {@inheritdoc}
     */
    public function setCountryCode($countryCode)
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * {@inheritdoc}
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPostalCodePattern()
    {
        return $this->postalCodePattern;
    }

    /**
     * {@inheritdoc}
     */
    public function setPostalCodePattern($postalCodePattern)
    {
        $this->postalCodePattern = $postalCodePattern;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPostalCodePatternType()
    {
        return $this->postalCodePatternType;
    }

    /**
     * {@inheritdoc}
     */
    public function setPostalCodePatternType($postalCodePatternType)
    {
        PatternType::assertExists($postalCodePatternType);
        $this->postalCodePatternType = $postalCodePatternType;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * {@inheritdoc}
     */
    public function setChildren(Collection $children)
    {
        $this->children = $children;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasChildren()
    {
        return !$this->children->isEmpty();
    }

    /**
     * Gets the locale.
     *
     * @return string The locale.
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Sets the locale.
     *
     * @param string $locale The locale.
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }
}
