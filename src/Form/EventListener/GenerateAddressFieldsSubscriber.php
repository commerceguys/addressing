<?php

namespace CommerceGuys\Addressing\Form\EventListener;

use CommerceGuys\Addressing\Enum\AddressField;
use CommerceGuys\Addressing\Enum\AdministrativeAreaType;
use CommerceGuys\Addressing\Enum\DependentLocalityType;
use CommerceGuys\Addressing\Enum\LocalityType;
use CommerceGuys\Addressing\Enum\PostalCodeType;
use CommerceGuys\Addressing\Model\AddressFormatInterface;
use CommerceGuys\Addressing\Provider\DataProviderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class GenerateAddressFieldsSubscriber implements EventSubscriberInterface
{
    /**
     * The data provider.
     *
     * @var DataProviderInterface
     */
    protected $dataProvider;

    /**
     * The mapping between field constants (format) and field names (address).
     *
     * @var array
     */
    protected $fieldMapping = [
        AddressField::ADMINISTRATIVE_AREA => 'administrativeArea',
        AddressField::LOCALITY => 'locality',
        AddressField::DEPENDENT_LOCALITY => 'dependentLocality',
        AddressField::POSTAL_CODE => 'postalCode',
        AddressField::SORTING_CODE => 'sortingCode',
        AddressField::ADDRESS => 'addressLine1',
        AddressField::ORGANIZATION => 'organization',
        AddressField::RECIPIENT => 'recipient',
    ];

    /**
     * Creates a GenerateAddressFieldsSubscriber instance.
     *
     * @param DataProviderInterface $dataProvider The data provider.
     */
    public function __construct(DataProviderInterface $dataProvider)
    {
        $this->dataProvider = $dataProvider;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::PRE_SUBMIT   => 'preSubmit'
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
        $addressFormat = $this->dataProvider->getAddressFormat($countryCode);
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
        foreach ($fields as $fieldConstant => $fieldOptions) {
            $type = isset($fieldOptions['choices']) ? 'choice' : 'text';
            $form->add($this->fieldMapping[$fieldConstant], $type, $fieldOptions);
        }
    }

    /**
     * Gets a list of form fields for the provided address format.
     *
     * @param AddressFormatInterface $addressFormat
     * @param array                  $subdivisions  An array of needed subdivisions.
     *
     * @return array An array in the $fieldConstant => $formOptions format.
     */
    protected function getFormFields(AddressFormatInterface $addressFormat, $subdivisions)
    {
        // @todo Add support for having multiple fields in the same line.
        $fields = [];
        $labels = $this->getFieldLabels($addressFormat);
        $requiredFields = $addressFormat->getRequiredFields();
        $parsedFormat = explode("\n", $addressFormat->getFormat());
        foreach ($parsedFormat as $formatLine) {
            foreach ($this->fieldMapping as $fieldConstant => $fieldName) {
                if (strpos($formatLine, '%' . $fieldConstant) !== FALSE) {
                    $fields[$fieldConstant] = [
                        'label' => $labels[$fieldConstant],
                        'required' => in_array($fieldConstant, $requiredFields),
                    ];
                }
            }
        }

        // Add choices for predefined subdivisions.
        foreach ($subdivisions as $fieldConstant => $parentId) {
            // @todo Pass the form locale to get the translated values.
            $children = $this->dataProvider->getSubdivisions($addressFormat->getCountryCode(), $parentId);
            if ($children) {
                $fields[$fieldConstant]['choices'] = [];
                foreach ($children as $child) {
                    $fields[$fieldConstant]['choices'][$child->getId()] = $child->getName();
                }
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
        $dependentLocalityType = $addressFormat->getLocalityType();
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
            AddressField::ADDRESS => 'Street Address',
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
