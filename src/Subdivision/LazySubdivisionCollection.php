<?php

namespace CommerceGuys\Addressing\Subdivision;

use Doctrine\Common\Collections\AbstractLazyCollection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * A lazy collection that loads the subdivisions on demand.
 */
class LazySubdivisionCollection extends AbstractLazyCollection
{
    /**
     * The parents.
     *
     * @var array
     */
    protected $parents;

    /**
     * The subdivision repository.
     *
     * @var SubdivisionRepositoryInterface
     */
    protected $repository;

    /**
     * Creates a LazySubdivisionCollection instance.
     *
     * @param array $parents The parents (country code, subdivision codes).
     */
    public function __construct(array $parents)
    {
        $this->parents = $parents;
    }

    /**
     * {@inheritdoc}
     */
    protected function doInitialize(): void
    {
        $repository = $this->getRepository();
        $subdivisions = $repository->getAll($this->parents);
        $this->collection = new ArrayCollection($subdivisions);
    }

    public function getRepository(): SubdivisionRepositoryInterface
    {
        return $this->repository;
    }

    public function setRepository(SubdivisionRepositoryInterface $repository): void
    {
        $this->repository = $repository;
    }
}
