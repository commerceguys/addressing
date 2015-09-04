<?php

namespace CommerceGuys\Addressing\Collection;

use CommerceGuys\Addressing\Repository\SubdivisionRepositoryInterface;
use Doctrine\Common\Collections\AbstractLazyCollection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * A lazy collection that loads the subdivisions on demand.
 */
class LazySubdivisionCollection extends AbstractLazyCollection
{
    /**
     * The country code.
     *
     * @var string
     */
    protected $countryCode;

    /**
     * The parent subdivision id.
     *
     * @var string
     */
    protected $parentId;

    /**
     * The locale.
     *
     * @var string
     */
    protected $locale;

    /**
     * The subdivision repository.
     *
     * @var SubdivisionRepositoryInterface
     */
    protected $repository;

    /**
     * Creates a LazySubdivisionCollection instance.
     *
     * @param string      $countryCode The country code.
     * @param string      $parentId    The parent subdivision id.
     * @param string|null $locale      The locale (e.g. fr-FR).
     */
    public function __construct($countryCode, $parentId, $locale = null)
    {
        $this->countryCode = $countryCode;
        $this->parentId = $parentId;
        $this->locale = $locale;
    }

    /**
     * {@inheritdoc}
     */
    protected function doInitialize()
    {
        $repository = $this->getRepository();
        $subdivisions = $repository->getAll($this->countryCode, $this->parentId, $this->locale);
        $this->collection = new ArrayCollection($subdivisions);
    }

    /**
     * Gets the subdivision repository.
     *
     * @return SubdivisionRepositoryInterface The subdivision repository.
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * Sets the subdivision repository.
     *
     * @param SubdivisionRepositoryInterface $repository The subdivision repository.
     */
    public function setRepository(SubdivisionRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }
}
