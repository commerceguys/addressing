<?php

namespace CommerceGuys\Addressing\Country;

/**
 * Represents a country.
 */
final class Country
{
    /**
     * The two-letter country code.
     *
     * @var string
     */
    protected $countryCode;

    /**
     * The country name.
     *
     * @var string
     */
    protected $name;

    /**
     * The three-letter country code.
     *
     * @var string
     */
    protected $threeLetterCode;

    /**
     * The numeric country code.
     *
     * @var string
     */
    protected $numericCode;

    /**
     * The currency code.
     *
     * @var string
     */
    protected $currencyCode;

    /**
     * The locale (i.e. "en_US").
     *
     * @var string
     */
    protected $locale;

    public function __construct(array $definition)
    {
        foreach (['country_code', 'name', 'locale'] as $requiredProperty) {
            if (empty($definition[$requiredProperty])) {
                throw new \InvalidArgumentException(sprintf('Missing required property "%s".', $requiredProperty));
            }
        }

        $this->countryCode = $definition['country_code'];
        $this->name = $definition['name'];
        if (isset($definition['three_letter_code'])) {
            $this->threeLetterCode = $definition['three_letter_code'];
        }
        if (isset($definition['numeric_code'])) {
            $this->numericCode = $definition['numeric_code'];
        }
        if (isset($definition['currency_code'])) {
            $this->currencyCode = $definition['currency_code'];
        }
        $this->locale = $definition['locale'];
    }

    /**
     * Gets the string representation of the Country.
     */
    public function __toString(): string
    {
        return $this->countryCode;
    }

    /**
     * Gets the two-letter country code.
     */
    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    /**
     * Gets the country name.
     *
     * This value is locale specific.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Gets the three-letter country code.
     *
     * Note that not every country has a three-letter code.
     * CLDR lists "Canary Islands" (IC) and "Ceuta and Melilla" (EA)
     * as separate countries, even though they are formally a part of Spain
     * and have no three-letter or numeric ISO codes.
     */
    public function getThreeLetterCode(): ?string
    {
        return $this->threeLetterCode;
    }

    /**
     * Gets the numeric country code.
     *
     * The numeric code has three digits, and the first one can be a zero,
     * hence the need to pass it around as a string.
     *
     * Note that not every country has a numeric code.
     * CLDR lists "Canary Islands" (IC) and "Ceuta and Melilla" (EA)
     * as separate countries, even though they are formally a part of Spain
     * and have no three-letter or numeric ISO codes.
     * "Ascension Island" (AE) also has no numeric code, even though it has a
     * three-letter code.
     *
     * @return string|null
     */
    public function getNumericCode(): ?string
    {
        return $this->numericCode;
    }

    /**
     * Gets the currency code.
     *
     * Represents the official currency used in the country, if known.
     *
     * @return string|null
     */
    public function getCurrencyCode(): ?string
    {
        return $this->currencyCode;
    }

    /**
     * Gets the timezones.
     *
     * Note that a country can span more than one timezone.
     * For example, Germany has ['Europe/Berlin', 'Europe/Busingen'].
     *
     * @return string[]
     */
    public function getTimezones(): array
    {
        return \DateTimeZone::listIdentifiers(\DateTimeZone::PER_COUNTRY, $this->countryCode);
    }

    /**
     * Gets the locale.
     *
     * The country name is locale specific.
     */
    public function getLocale(): string
    {
        return $this->locale;
    }
}
