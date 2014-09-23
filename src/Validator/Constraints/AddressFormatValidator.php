<?php

namespace CommerceGuys\Addressing\Validator\Constraints;

use CommerceGuys\Addressing\Metadata\AddressMetadataRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class AddressFormatValidator extends ConstraintValidator
{
    /**
     * The metadata repository.
     *
     * @var AddressMetadataInterface
     */
    protected $repository;

    /**
     * {@inheritDoc}
     */
    public function validate($value, Constraint $constraint)
    {
        $address = $value;
        $countryCode = $address->getCountryCode();
        $repository = $this->getRepository();
        $addressFormat = $repository->getAddressFormat($countryCode);
    }

    /**
     * Gets the metadata repository.
     *
     * @return AddressMetadataRepositoryInterface The metadata repository.
     */
    public function getRepository()
    {
        if (!$this->repository) {
            $this->repository = new AddressMetadataRepository();
        }

        return $this->repository;
    }

    /**
     * Sets the metadata repository.
     *
     * @param AddressMetadataRepositoryInterface $repository The metadata repository.
     */
    public function setRepository(AddressMetadataRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }
}
