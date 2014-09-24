<?php

namespace CommerceGuys\Addressing\Form\Type;

use CommerceGuys\Addressing\Form\EventListener\GenerateAddressFieldsSubscriber;
use CommerceGuys\Addressing\Metadata\AddressMetadataRepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AddressType extends AbstractType
{
    /**
     * The metadata repository.
     *
     * @var AddressMetadataInterface
     */
    protected $repository;

    /**
     * Creates an AddressType instance.
     *
     * @param AddressMetadataRepositoryInterface $repository The metadata repository.
     */
    public function __construct(AddressMetadataRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('countryCode', 'choice', array(
            'choices' => $this->repository->getCountryNames(),
            'required' => true,
        ));
        $builder->addEventSubscriber(new GenerateAddressFieldsSubscriber($this->repository));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'CommerceGuys\Addressing\Address'
        ));
    }

    public function getName()
    {
        return 'address';
    }
}
