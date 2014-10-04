<?php

namespace CommerceGuys\Addressing\Metadata;

class Subdivision implements SubdivisionInterface
{
    /**
     * The parent.
     *
     * @var SubdivisionInterface
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
     * The children.
     *
     * @param SubdivisionInterface[]
     */
    protected $children;

    /**
     * The locale.
     *
     * @var string
     */
    protected $locale;

    /**
     * The metadata repository.
     *
     * @var AddressMetadataRepositoryInterface
     */
    protected static $repository;

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        if (!$this->parent->getCode()) {
            // The parent object is incomplete. Load the full one.
            $repository = self::getRepository();
            $this->parent = $repository->getSubdivision($this->parent->getId());
        }

        return $this->parent;
    }

    /**
     * {@inheritdoc}
     */
    public function setParent(SubdivisionInterface $parent)
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
    public function getChildren()
    {
        // When a subdivision has children the metadata repository sets $children
        // to array('load'), to indicate that they should be lazy loaded.
        if (!isset($this->children) || $this->children === array('load')) {
            $repository = self::getRepository();
            $this->children = $repository->getSubdivisions($this->countryCode, $this->id, $this->locale);
        }

        return $this->children;
    }

    /**
     * {@inheritdoc}
     */
    public function setChildren($children)
    {
        $this->children = $children;
    }

    /**
     * {@inheritdoc}
     */
    public function hasChildren()
    {
        return !empty($this->children);
    }

    /**
     * {@inheritdoc}
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * {@inheritdoc}
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public static function getRepository()
    {
        if (!isset(self::$repository)) {
            self::setRepository(new AddressMetadataRepository());
        }

        return self::$repository;
    }

    /**
     * {@inheritdoc}
     */
    public static function setRepository(AddressMetadataRepositoryInterface $repository)
    {
        self::$repository = $repository;
    }
}
