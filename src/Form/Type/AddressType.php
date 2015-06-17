<?php

namespace CommerceGuys\Addressing\Form\Type;

use CommerceGuys\Addressing\Form\EventListener\GenerateAddressFieldsSubscriber;
use CommerceGuys\Addressing\Repository\AddressFormatRepositoryInterface;
use CommerceGuys\Addressing\Repository\CountryRepositoryInterface;
use CommerceGuys\Addressing\Repository\SubdivisionRepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddressType extends AbstractType
{
    /**
     * The address format repository.
     *
     * @var AddressFormatRepositoryInterface
     */
    protected $addressFormatRepository;

    /**
     * The country repository.
     *
     * @var CountryRepositoryInterface
     */
    protected $countryRepository;

    /**
     * The subdivision repository.
     *
     * @var SubdivisionRepositoryInterface
     */
    protected $subdivisionRepository;

    /**
     * Creates an AddressType instance.
     *
     * @param AddressFormatRepositoryInterface $addressFormatRepository
     * @param CountryRepositoryInterface       $countryRepository
     * @param SubdivisionRepositoryInterface   $subdivisionRepository
     */
    public function __construct(AddressFormatRepositoryInterface $addressFormatRepository, CountryRepositoryInterface $countryRepository, SubdivisionRepositoryInterface $subdivisionRepository)
    {
        $this->addressFormatRepository = $addressFormatRepository;
        $this->countryRepository = $countryRepository;
        $this->subdivisionRepository = $subdivisionRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('countryCode', 'choice', [
            'choices' => $this->countryRepository->getList(),
            'required' => true,
        ]);
        $builder->addEventSubscriber(new GenerateAddressFieldsSubscriber($this->addressFormatRepository, $this->subdivisionRepository));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => 'CommerceGuys\Addressing\Model\Address']);
    }

    public function getName()
    {
        return 'address';
    }
}
