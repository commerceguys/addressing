<?php

namespace CommerceGuys\Addressing\Form\EventListener;

use CommerceGuys\Addressing\Enum\AddressField;
use CommerceGuys\Addressing\Enum\AdministrativeAreaType;
use CommerceGuys\Addressing\Enum\DependentLocalityType;
use CommerceGuys\Addressing\Enum\LocalityType;
use CommerceGuys\Addressing\Enum\PostalCodeType;
use CommerceGuys\Addressing\Model\AddressFormatInterface;
use CommerceGuys\Addressing\Repository\AddressFormatRepositoryInterface;
use CommerceGuys\Addressing\Repository\SubdivisionRepositoryInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class GenerateAddressFieldsSubscriber implements EventSubscriberInterface
{
    /**
     * The address format repository.
     *
     * @var AddressFormatRepositoryInterface
     */
    protected $addressFormatRepository;

    /**
     * The subdivision repository.
     *
     * @var SubdivisionRepositoryInterface
     */
    protected $subdivisionRepository;

    /**
     * Creates a GenerateAddressFieldsSubscriber instance.
     *
     * @param AddressFormatRepositoryInterface $addressFormatRepository
     * @param SubdivisionRepositoryInterface   $subdivisionRepository
     */
    public function __construct(AddressFormatRepositoryInterface $addressFormatRepository, SubdivisionRepositoryInterface $subdivisionRepository)
    {
        $this->addressFormatRepository = $addressFormatRepository;
        $this->subdivisionRepository = $subdivisionRepository;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::PRE_SUBMIT => 'preSubmit',
        ];
    }

    public function preSetData(FormEvent $event)
    {
        $address = $event->getData();
        $form = $event->getForm();
        if (null === $address) {
            return;
        }

        $countryCode = $address->getCountryCode();
        $administrativeArea = $address->getAdministrativeArea();
        $locality = $address->getLocality();

        $this->buildForm($form, $countryCode, $administrativeArea, $locality);
    }

    public function preSubmit(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();
        $countryCode = array_key_exists('countryCode', $data) ? $data['countryCode'] : null;
        $administrativeArea = array_key_exists('administrativeArea', $data) ? $data['administrativeArea'] : null;
        $locality = array_key_exists('locality', $data) ? $data['locality'] : null;

        $this->buildForm($form, $countryCode, $administrativeArea, $locality);
    }

    /**
     * Builds the address form for the provided country code.
     *
     * @param string $countryCode        The country code.
     * @param string $administrativeArea The administrative area.
     * @param string $locality           The locality.
     */
    protected function buildForm($form, $countryCode, $administrativeArea, $locality)
    {
        $addressFormat = $this->addressFormatRepository->get($countryCode);
        // A list of needed subdivisions and their parent ids.
        $subdivisions = [
            AddressField::ADMINISTRATIVE_AREA => 0,
        ];
        if (!empty($administrativeArea)) {
            $subdivisions[AddressField::LOCALITY] = $administrativeArea;
        }
        if (!empty($locality)) {
            $subdivisions[AddressField::DEPENDENT_LOCALITY] = $locality;
        }

        $fields = $this->getFormFields($addressFormat, $subdivisions);
        foreach ($fields as $field => $fieldOptions) {
            $type = isset($fieldOptions['choices']) ? 'choice' : 'text';
            $form->add($field, $type, $fieldOptions);
        }
    }

    /**
     * Gets a list of form fields for the provided address format.
     *
     * @param AddressFormatInterface $addressFormat
     * @param array                  $subdivisions  An array of needed subdivisions.
     *
     * @return array An array in the $field => $formOptions format.
     */
    protected function getFormFields(AddressFormatInterface $addressFormat, $subdivisions)
    {
        // @todo Add support for having multiple fields in the same line.
        $fields = [];
        $labels = $this->getFieldLabels($addressFormat);
        $requiredFields = $addressFormat->getRequiredFields();
        $groupedFields = $addressFormat->getGroupedFields();
        foreach ($groupedFields as $lineFields) {
            foreach ($lineFields as $field) {
                $fields[$field] = [
                    'label' => $labels[$field],
                    'required' => in_array($field, $requiredFields),
                ];
            }
        }

        // Add choices for predefined subdivisions.
        foreach ($subdivisions as $field => $parentId) {
            // @todo Pass the form locale to get the translated values.
            $children = $this->subdivisionRepository->getList($addressFormat->getCountryCode(), $parentId);
            if ($children) {
                $fields[$field]['choices'] = $children;
            }
        }

        return $fields;
    }

    /**
     * Gets the labels for the provided address format's fields.
     *
     * @param AddressFormatInterface $addressFormat
     *
     * @return array An array of labels keyed by field constants.
     */
    protected function getFieldLabels($addressFormat)
    {
        // All possible subdivision labels.
        $subdivisionLabels = [
            AdministrativeAreaType::AREA => 'Area',
            AdministrativeAreaType::COUNTY => 'County',
            AdministrativeAreaType::DEPARTMENT => 'Department',
            AdministrativeAreaType::DISTRICT => 'District',
            AdministrativeAreaType::DO_SI => 'Do',
            AdministrativeAreaType::EMIRATE => 'Emirate',
            AdministrativeAreaType::ISLAND => 'Island',
            AdministrativeAreaType::OBLAST => 'Oblast',
            AdministrativeAreaType::PARISH => 'Parish',
            AdministrativeAreaType::PREFECTURE => 'Prefecture',
            AdministrativeAreaType::PROVINCE => 'Province',
            AdministrativeAreaType::STATE => 'State',
            LocalityType::CITY => 'City',
            LocalityType::DISTRICT => 'District',
            LocalityType::POST_TOWN => 'Post Town',
            DependentLocalityType::DISTRICT => 'District',
            DependentLocalityType::NEIGHBORHOOD => 'Neighborhood',
            DependentLocalityType::VILLAGE_TOWNSHIP => 'Village / Township',
            DependentLocalityType::SUBURB => 'Suburb',
            PostalCodeType::POSTAL => 'Postal Code',
            PostalCodeType::ZIP => 'ZIP code',
            PostalCodeType::PIN => 'PIN code',
        ];

        // Determine the correct administrative area label.
        $administrativeAreaType = $addressFormat->getAdministrativeAreaType();
        $administrativeAreaLabel = '';
        if (isset($subdivisionLabels[$administrativeAreaType])) {
            $administrativeAreaLabel = $subdivisionLabels[$administrativeAreaType];
        }
        // Determine the correct locality label.
        $localityType = $addressFormat->getLocalityType();
        $localityLabel = '';
        if (isset($subdivisionLabels[$localityType])) {
            $localityLabel = $subdivisionLabels[$localityType];
        }
        // Determine the correct dependent locality label.
        $dependentLocalityType = $addressFormat->getDependentLocalityType();
        $dependentLocalityLabel = '';
        if (isset($subdivisionLabels[$dependentLocalityType])) {
            $dependentLocalityLabel = $subdivisionLabels[$dependentLocalityType];
        }
        // Determine the correct postal code label.
        $postalCodeType = $addressFormat->getPostalCodeType();
        $postalCodeLabel = $subdivisionLabels[PostalCodeType::POSTAL];
        if (isset($subdivisionLabels[$postalCodeType])) {
            $postalCodeLabel = $subdivisionLabels[$postalCodeType];
        }

        // Assemble the final set of labels.
        $labels = [
            AddressField::ADMINISTRATIVE_AREA => $administrativeAreaLabel,
            AddressField::LOCALITY => $localityLabel,
            AddressField::DEPENDENT_LOCALITY => $dependentLocalityLabel,
            AddressField::ADDRESS_LINE1 => 'Street Address',
            AddressField::ADDRESS_LINE2 => false,
            AddressField::ORGANIZATION => 'Company',
            AddressField::RECIPIENT => 'Contact Name',
            // Google's libaddressinput provides no label for this field type,
            // Google wallet calls it "CEDEX" for every country that uses it.
            AddressField::SORTING_CODE => 'Cedex',
            AddressField::POSTAL_CODE => $postalCodeLabel,
        ];

        return $labels;
    }
}
