<?php

namespace CommerceGuys\Addressing\Model;

class Address implements AddressInterface
{
    /**
     * The locale.
     *
     * @var string
     */
    protected $locale;

    /**
     * The two-letter country code.
     *
     * @var string
     */
    protected $countryCode;

    /**
     * The top-level administrative subdivision of the country.
     *
     * @var string
     */
    protected $administrativeArea;

    /**
     * The locality (i.e. city).
     *
     * @var string
     */
    protected $locality;

    /**
     * The dependent locality (i.e. neighbourhood).
     *
     * @var string
     */
    protected $dependentLocality;

    /**
     * The postal code.
     *
     * @var string
     */
    protected $postalCode;

    /**
     * The sorting code.
     *
     * @var string
     */
    protected $sortingCode;

    /**
     * The first line of the address block.
     *
     * @var string
     */
    protected $addressLine1;

    /**
     * The second line of the address block.
     *
     * @var string
     */
    protected $addressLine2;

    /**
     * The organization.
     *
     * @var string
     */
    protected $organization;

    /**
     * The recipient.
     *
     * @var string
     */
    protected $recipient;

    /**
     * {@inheritdoc}
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * {@inheritdoc}
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * {@inheritdoc}
     */
    public function setCountryCode($countryCode)
    {
        $this->countryCode = $countryCode;

        return $this;
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
    public function setAdministrativeArea($administrativeArea)
    {
        $this->administrativeArea = $administrativeArea;

        return $this;
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
    public function setLocality($locality)
    {
        $this->locality = $locality;

        return $this;
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
    public function setDependentLocality($dependentLocality)
    {
        $this->dependentLocality = $dependentLocality;

        return $this;
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
    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;

        return $this;
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
    public function setSortingCode($sortingCode)
    {
        $this->sortingCode = $sortingCode;

        return $this;
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
    public function setAddressLine1($addressLine1)
    {
        $this->addressLine1 = $addressLine1;

        return $this;
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
    public function setAddressLine2($addressLine2)
    {
        $this->addressLine2 = $addressLine2;

        return $this;
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
    public function setOrganization($organization)
    {
        $this->organization = $organization;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRecipient()
    {
        return $this->recipient;
    }

    /**
     * {@inheritdoc}
     */
    public function setRecipient($recipient)
    {
        $this->recipient = $recipient;

        return $this;
    }
}
