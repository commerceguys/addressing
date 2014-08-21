<?php

namespace CommerceGuys\Address\Validator;

use Symfony\Component\Validator\LoaderInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class ValidatorLoader implements LoaderInterface
{
    /**
     * @var AddressMetadataStore
     */
    protected $metadataStore;

    public function __construct(AddressMetadataStore $metadataStore)
    {
        $this->metadataStore = $metadataStore;
    }

    /**
     * {@inheritdoc}
     */
    public function loadClassMetadata(ClassMetadata $metadata)
    {
        $reflClass = $metadata->getReflectionClass();
        $className = $reflClass->name;

        $metadata->setGroupSequence(array(
            'Country',
            'AdministrativeArea',
            'Locality',
            'DependentLocality',
            'Default',
        ));

        $metadata->addConstraint(Address());
    }
}
