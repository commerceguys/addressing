addressing
==========

[![Build Status](https://travis-ci.org/commerceguys/addressing.svg?branch=master)](https://travis-ci.org/commerceguys/addressing)

A PHP 5.4+ addressing library, powered by Google's dataset.

Stores and manipulates postal addresses, meant to identify a precise recipient location for shipping or billing purposes.

Features:
- Address formats for 200 countries
- Subdivisions (administrative areas, localities, dependent localities) for 40 countries
- Subdivision translations for all of the parent country's (i.e Canada, Switzerland) official languages.
- Validation via symfony/validator
- Form generation via symfony/form (Experimental, unfinished)
- Postal formatting
- Zones via the [commerceguys/zone](https://github.com/commerceguys/zone) library.

The dataset is [stored locally](https://github.com/commerceguys/addressing/tree/master/resources) in JSON format, [generated](https://github.com/commerceguys/addressing/blob/master/scripts/generate.php) from Google's [Address Data Service](https://i18napis.appspot.com/address).

The CLDR country list is used (via [symfony/intl](https://github.com/symfony/intl) or [commerceguys/intl](https://github.com/commerceguys/intl)), because it includes additional countries for addressing purposes, such as Canary Islands (IC).

Further backstory can be found in [this blog post](https://drupalcommerce.org/blog/16864/commerce-2x-stories-addressing).

# Data model

The [address interface](https://github.com/commerceguys/addressing/blob/master/src/Model/AddressInterface.php) represents a postal adddress, with getters for the following fields:

- Country
- Administrative area
- Locality (City)
- Dependent Locality
- Postal code
- Sorting code
- Address line 1
- Address line 2
- Organization
- Recipient

Field names follow the OASIS [eXtensible Address Language (xAL)](http://www.oasis-open.org/committees/ciq/download.shtml) standard.

The interface makes no assumptions about mutability.
The implementing application can extend the interface to provide setters, or implement a value object that uses either [PSR-7 style with* mutators](https://github.com/commerceguys/addressing/blob/master/src/Model/ImmutableAddressInterface) or relies on an AddressBuilder.
A default [address value object](https://github.com/commerceguys/addressing/blob/master/src/Model/Address.php) is provided that can be used as an example, or mapped by Doctrine (preferably as an embeddable).

The [address format interface](https://github.com/commerceguys/addressing/blob/master/src/Model/AddressFormatInterface.php) has getters for the following country-specific metadata:

- Which fields are used, and in which order
- Which fields are required
- Which fields need to be uppercased for the actual mailing (to facilitate automated sorting of mail)
- The labels for the administrative area (state, province, parish, etc.), locality (city/post town/district, etc.), dependent locality (neighborhood, suburb, district, etc) and the postal code (postal code or ZIP code)
- The regular expression pattern for validating postal codes

The [subdivision interface](https://github.com/commerceguys/addressing/blob/master/src/Model/SubdivisionInterface.php) has getters for the following data:

- The subdivision code (used to represent the subdivison on a parcel/envelope, e.g. CA for California)
- The subdivison name (shown to the user in a dropdown)
- The postal code prefix (used to ensure that a postal code begins with the expected characters)

Subdivisions are hierarchical and can have up to three levels:
Administrative Area -> Locality -> Dependent Locality.

```php
use CommerceGuys\Addressing\Repository\AddressFormatRepository;
use CommerceGuys\Addressing\Repository\SubdivisionRepository;

$addressFormatRepository = new AddressFormatRepository();
$subdivisionRepository = new SubdivisionRepository();

// Get the address format for Brazil.
$addressFormat = $addressFormatRepository->get('BR');

// Get the subdivisions for Brazil.
$states = $subdivisionRepository->getAll('BR');
foreach ($states as $state) {
    $municipalities = $state->getChildren();
}

// Get the subdivisions for Canada, in French.
$states = $subdivisionRepository->getAll('CA', 0, 'fr');
foreach ($states as $state) {
    echo $state->getName();
}
```

# Formatters

Addresses are formatted according to the address format, in HTML or text.

## DefaultFormatter

Formats an address for display, always adds the localized country name.

```php
use CommerceGuys\Addressing\Formatter\DefaultFormatter;
use CommerceGuys\Addressing\Repository\AddressFormatRepository;
use CommerceGuys\Addressing\Repository\CountryRepository;
use CommerceGuys\Addressing\Repository\SubdivisionRepository;

$addressFormatRepository = new AddressFormatRepository();
$countryRepository = new CountryRepository();
$subdivisionRepository = new SubdivisionRepository();
$formatter = new DefaultFormatter($addressFormatRepository, $countryRepository, $subdivisionRepository);
// Options passed to the constructor or setOption / setOptions allow turning
// off html rendering, customizing the wrapper element and its attributes.

$address = new Address();
$address = $address
    ->withCountryCode('US')
    ->withAdministrativeArea('US-CA')
    ->withLocality('Mountain View')
    ->withAddressLine1('1098 Alta Ave');

echo $formatter->format($address);

/** Output:
<p translate="no">
<span class="address-line1">1098 Alta Ave</span><br>
<span class="locality">Mountain View</span>, <span class="administrative-area">CA</span><br>
<span class="country">United States</span>
</p>
**/
```

## PostalLabelFormatter

Takes care of uppercasing fields where required by the format (to faciliate automated mail sorting).

Requires specifying the origin country code, allowing it to differentiate between domestic and international mail.
In case of domestic mail, the country name is not displayed at all.
In case of international mail:

1. The postal code is prefixed with the destination's postal code prefix.
2. The country name is added to the formatted address, in both the current locale and English.
This matches the recommandation given by the Universal Postal Union, to avoid difficulties in countries of transit.

```php
use CommerceGuys\Addressing\Formatter\PostalLabelFormatter;
use CommerceGuys\Addressing\Repository\AddressFormatRepository;
use CommerceGuys\Addressing\Repository\CountryRepository;
use CommerceGuys\Addressing\Repository\SubdivisionRepository;

$addressFormatRepository = new AddressFormatRepository();
$countryRepository = new CountryRepository();
$subdivisionRepository = new SubdivisionRepository();
// Defaults to text rendering. Requires setting the origin country code
// (e.g. 'FR') through the constructor or the setter, before calling format().
$formatter = new PostalLabelFormatter($addressFormatRepository, $countryRepository, $subdivisionRepository, 'FR', 'fr');

$address = new Address();
$address = $address
    ->withCountryCode('US')
    ->withAdministrativeArea('US-CA')
    ->withLocality('Mountain View')
    ->withAddressLine1('1098 Alta Ave');

echo $formatter->format($address);

/** Output:
1098 Alta Ave
MOUNTAIN VIEW, CA 94043
ÉTATS-UNIS - UNITED STATES
**/
```

# Validator

Address validation relies on the [Symfony Validator](https://github.com/symfony/validator) library.

Checks performed:
- All required fields are filled in.
- All fields unused by the country's format are empty.
- All subdivisions are valid (values matched against predefined subdivisions).
- The postal code is valid (country and subdivision-level patterns).

```php
use CommerceGuys\Addressing\Model\Address;
use CommerceGuys\Addressing\Validator\Constraints\AddressFormat;
use CommerceGuys\Addressing\Validator\Constraints\Country;
use Symfony\Component\Validator\Validation;

$address = new Address('FR');

$validator = Validation::createValidator();
// Validate the country code, then validate the rest of the address.
$violations = $validator->validateValue($address->getCountryCode(), new Country());
if (!$violations->count()) {
  $violations = $validator->validateValue($address, new AddressFormat());
}
```

# Integrations

- [Drupal module](https://drupal.org/project/address)
