<?php

namespace CommerceGuys\Addressing\Form\Type;

use CommerceGuys\Addressing\Form\EventListener\GenerateAddressFieldsSubscriber;
use CommerceGuys\Addressing\Provider\DataProviderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AddressType extends AbstractType
{
    /**
     * The data provider.
     *
     * @var DataProviderInterface
     */
    protected $dataProvider;

    /**
     * Creates an AddressType instance.
     *
     * @param DataProviderInterface $dataProvider The data provider.
     */
    public function __construct(DataProviderInterface $dataProvider)
    {
        $this->dataProvider = $dataProvider;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('countryCode', 'choice', [
            'choices' => $this->dataProvider->getCountryNames(),
            'required' => true,
        ]);
        $builder->addEventSubscriber(new GenerateAddressFieldsSubscriber($this->dataProvider));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(['data_class' => 'CommerceGuys\Addressing\Model\Address']);
    }

    public function getName()
    {
        return 'address';
    }
}
