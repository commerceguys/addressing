<?php

namespace CommerceGuys\Addressing\Form\EventListener;

use CommerceGuys\Addressing\Metadata\AddressFormat;
use CommerceGuys\Addressing\Metadata\AddressFormatInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class GenerateAddressFieldsSubscriber implements EventSubscriberInterface
{
    /**
     * The metadata repository.
     *
     * @var AddressMetadataInterface
     */
    protected $repository;

    /**
     * The mapping between field constants (format) and field names (address).
     *
     * @var array
     */
    protected $fieldMapping = array(
        AddressFormatInterface::FIELD_ADMINISTRATIVE_AREA => 'administrativeArea',
        AddressFormatInterface::FIELD_LOCALITY => 'locality',
        AddressFormatInterface::FIELD_DEPENDENT_LOCALITY => 'dependentLocality',
        AddressFormatInterface::FIELD_POSTAL_CODE => 'postalCode',
        AddressFormatInterface::FIELD_SORTING_CODE => 'sortingCode',
        AddressFormatInterface::FIELD_ADDRESS => 'addressLine1',
        AddressFormatInterface::FIELD_ORGANIZATION => 'organization',
        AddressFormatInterface::FIELD_RECIPIENT => 'recipient',
    );

    /**
     * Creates a GenerateAddressFieldsSubscriber instance.
     *
     * @param AddressMetadataRepositoryInterface $repository The metadata repository.
     */
    public function __construct($repository)
    {
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::PRE_SUBMIT   => 'preSubmit'
        );
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
        $addressFormat = $this->repository->getAddressFormat($countryCode);
        // A list of needed subdivisions and their parent ids.
        $subdivisions = array(
            AddressFormatInterface::FIELD_ADMINISTRATIVE_AREA => 0,
        );
        if (!empty($administrativeArea)) {
            $subdivisions[AddressFormatInterface::FIELD_LOCALITY] = $administrativeArea;
        }
        if (!empty($locality)) {
            $subdivisions[AddressFormatInterface::FIELD_DEPENDENT_LOCALITY] = $locality;
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
        $fields = array();
        $labels = $this->getFieldLabels($addressFormat);
        $requiredFields = $addressFormat->getRequiredFields();
        $parsedFormat = explode("\n", $addressFormat->getFormat());
        foreach ($parsedFormat as $formatLine) {
            foreach ($this->fieldMapping as $fieldConstant => $fieldName) {
                if (strpos($formatLine, '%' . $fieldConstant) !== FALSE) {
                    $fields[$fieldConstant] = array(
                        'label' => $labels[$fieldConstant],
                        'required' => in_array($fieldConstant, $requiredFields),
                    );
                }
            }
        }

        // Add choices for predefined subdivisions.
        foreach ($subdivisions as $fieldConstant => $parentId) {
            // @todo Pass the form locale to get the translated values.
            $children = $this->repository->getSubdivisions($addressFormat->getCountryCode(), $parentId);
            if ($children) {
                $fields[$fieldConstant]['choices'] = array();
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
        $subdivisionLabels = array(
            AddressFormatInterface::ADMINISTRATIVE_AREA_TYPE_AREA => 'Area',
            AddressFormatInterface::ADMINISTRATIVE_AREA_TYPE_COUNTY => 'County',
            AddressFormatInterface::ADMINISTRATIVE_AREA_TYPE_DEPARTMENT => 'Department',
            AddressFormatInterface::ADMINISTRATIVE_AREA_TYPE_DISTRICT => 'District',
            AddressFormatInterface::ADMINISTRATIVE_AREA_TYPE_DO_SI => 'Do',
            AddressFormatInterface::ADMINISTRATIVE_AREA_TYPE_EMIRATE => 'emirate',
            AddressFormatInterface::ADMINISTRATIVE_AREA_TYPE_ISLAND => 'Island',
            AddressFormatInterface::ADMINISTRATIVE_AREA_TYPE_OBLAST => 'Oblast',
            AddressFormatInterface::ADMINISTRATIVE_AREA_TYPE_PARISH => 'Parish',
            AddressFormatInterface::ADMINISTRATIVE_AREA_TYPE_PREFECTURE => 'Prefecture',
            AddressFormatInterface::ADMINISTRATIVE_AREA_TYPE_PROVINCE => 'Province',
            AddressFormatInterface::ADMINISTRATIVE_AREA_TYPE_STATE => 'State',
            AddressFormatInterface::POSTAL_CODE_TYPE_POSTAL => 'Postal Code',
            AddressFormatInterface::POSTAL_CODE_TYPE_ZIP => 'ZIP code',
        );

        // Determine the correct administrative area label.
        $administrativeAreaType = $addressFormat->getAdministrativeAreaType();
        $administrativeAreaLabel = $subdivisionLabels[AddressFormatInterface::ADMINISTRATIVE_AREA_TYPE_STATE];
        if (isset($subdivisionLabels[$administrativeAreaType])) {
            $administrativeAreaLabel = $subdivisionLabels[$administrativeAreaType];
        }
        // Determine the correct postal code label.
        $postalCodeType = $addressFormat->getPostalCodeType();
        $postalCodeLabel = $subdivisionLabels[AddressFormatInterface::POSTAL_CODE_TYPE_POSTAL];
        if (isset($subdivisionLabels[$postalCodeType])) {
            $postalCodeLabel = $subdivisionLabels[$postalCodeType];
        }

        // Assemble the final set of labels.
        $labels = array(
            AddressFormatInterface::FIELD_ADMINISTRATIVE_AREA => $administrativeAreaLabel,
            AddressFormatInterface::FIELD_LOCALITY => 'City',
            AddressFormatInterface::FIELD_DEPENDENT_LOCALITY => 'District',
            AddressFormatInterface::FIELD_ADDRESS => 'Street Address',
            AddressFormatInterface::FIELD_ORGANIZATION => 'Company',
            AddressFormatInterface::FIELD_RECIPIENT => 'Contact Name',
            // Google's libaddressinput provides no label for this field type,
            // Google wallet calls it "CEDEX" for every country that uses it.
            AddressFormatInterface::FIELD_SORTING_CODE => 'Cedex',
            AddressFormatInterface::FIELD_POSTAL_CODE => $postalCodeLabel,
        );

        return $labels;
    }
}
