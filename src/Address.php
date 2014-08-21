<?php

namespace CommerceGuys\Address;

class Address implements AddressInterface
{
    /**
     * {@inheritdoc}
     */
    public function getPostalCountryCode()
    {
        return $this->postalCountryCode;
    }

    /**
     * {@inheritdoc}
     */
    public function getAdministrativeArea()
    {
        return $this->administrativeArea;
    }

    /**
     * {@inheritdoc}
     */
    public function getLocality()
    {
        return $this->locality;
    }

    /**
     * {@inheritdoc}
     */
    public function getDependentLocality()
    {
        return $this->dependentLocality;
    }

    /**
     * {@inheritdoc}
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * {@inheritdoc}
     */
    public function getSortingCode()
    {
        return $this->sortingCode;
    }

    /**
     * {@inheritdoc}
     */
    public function getAddressLine1()
    {
        return $this->addressLine1;
    }

    /**
     * {@inheritdoc}
     */
    public function getAddressLine2()
    {
        return $this->addressLine2;
    }

    /**
     * {@inheritdoc}
     */
    public function getAddressLine3()
    {
        return $this->addressLine3;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * {@inheritdoc}
     */
    public function getRecipient()
    {
        return $this->recipient;
    }
}
