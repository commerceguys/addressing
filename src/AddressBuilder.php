<?php

namespace CommerceGuys\Address;

class AddressBuilder extends Address
{
    /**
     * Build a new address.
     */
    public function __construct($postalCountryCode = null, $administrativeArea = null, $locality = null, $dependentLocality = null, $postalCode = null, $sortingCode = null, $addressLine1 = null, $addressLine2 = null, $addressLine3 = null, $organization = null, $recipient = null)
    {
        $this->postalCountryCode = $postalCountryCode;
        $this->postalCountryCode = $postalCountryCode;
        $this->administrativeArea = $administrativeArea;
        $this->locality = $locality;
        $this->dependentLocality = $dependentLocality;
        $this->postalCode = $postalCode;
        $this->sortingCode = $sortingCode;
        $this->addressLine1 = $addressLine1;
        $this->addressLine2 = $addressLine2;
        $this->addressLine3 = $addressLine3;
        $this->organization = $organization;
        $this->recipient = $recipient;
    }

    /**
     * {@inheritdoc}
     */
    public function setPostalCountryCode($postalCountryCode)
    {
        $this->postalCountryCode = $postalCountryCode;
    }

    /**
     * {@inheritdoc}
     */
    public function setAdministrativeArea($administrativeArea)
    {
        $this->administrativeArea = $administrativeArea;
    }

    /**
     * {@inheritdoc}
     */
    public function setLocality($locality)
    {
        $this->locality = $locality;
    }

    /**
     * {@inheritdoc}
     */
    public function setDependentLocality($dependentLocality)
    {
        $this->dependentLocality = $dependentLocality;
    }

    /**
     * {@inheritdoc}
     */
    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;
    }

    /**
     * {@inheritdoc}
     */
    public function setSortingCode($sortingCode)
    {
        $this->sortingCode = $sortingCode;
    }

    /**
     * {@inheritdoc}
     */
    public function getAddressLine1($addressLine1)
    {
        $this->addressLine1 = $addressLine1;
    }

    /**
     * {@inheritdoc}
     */
    public function setAddressLine2($addressLine2)
    {
        $this->addressLine2 = $addressLine2;
    }

    /**
     * {@inheritdoc}
     */
    public function setAddressLine3($addressLine3)
    {
        $this->addressLine3 = $addressLine3;
    }

    /**
     * {@inheritdoc}
     */
    public function setOrganization($organization)
    {
        $this->organization = $organization;
    }

    /**
     * {@inheritdoc}
     */
    public function setRecipient($recipient)
    {
        $this->recipient = $recipient;
    }
}

