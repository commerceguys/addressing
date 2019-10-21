addressing
==========

[![Build Status](https://travis-ci.org/commerceguys/addressing.svg?branch=master)](https://travis-ci.org/commerceguys/addressing)

A PHP 5.5+ addressing library, powered by CLDR and Google's address data.

Manipulates postal addresses, meant to identify a precise recipient location for shipping or billing purposes.

Features:
- Countries, with translations for over 250 locales
- Address formats for over 200 countries
- Subdivisions (administrative areas, localities, dependent localities) for 44 countries
- Both latin and local subdivision names, when relevant (e.g: Okinawa / 沖縄県)
- Formatting, in HTML or plain text.
- Validation via symfony/validator
- Zones

The dataset is [stored locally](https://github.com/commerceguys/addressing/tree/master/resources) in JSON format.
Countries are generated from [CLDR](http://cldr.unicode.org) v36. Address formats and subdivisions are generated from Google's [Address Data Service](https://chromium-i18n.appspot.com/ssl-address).

Further backstory can be found in [this blog post](https://drupalcommerce.org/blog/16864/commerce-2x-stories-addressing).

Also check out [commerceguys/intl](https://github.com/commerceguys/intl) for CLDR-powered languages/currencies/number formatting.

# Data model

The [address interface](https://github.com/commerceguys/addressing/blob/master/src/AddressInterface.php) represents a postal adddress, with getters for the following fields:

- Country code
- Administrative area
- Locality (City)
- Dependent Locality
- Postal code
- Sorting code
- Address line 1
- Address line 2
- Organization
- Given name (First name)
- Additional name (Middle name / Patronymic)
- Family name (Last name)

Field names follow the OASIS [eXtensible Address Language (xAL)](http://www.oasis-open.org/committees/ciq/download.shtml) standard.

The interface makes no assumptions about mutability.
The implementing application can extend the interface to provide setters, or implement a value object that uses either [PSR-7 style with* mutators](https://github.com/commerceguys/addressing/blob/master/src/ImmutableAddressInterface) or relies on an AddressBuilder.
A default [address value object](https://github.com/commerceguys/addressing/blob/master/src/Address.php) is provided that can be used as an example, or mapped by Doctrine (preferably as an embeddable).

The [address format](https://github.com/commerceguys/addressing/blob/master/src/AddressFormat/AddressFormat.php) provides the following information:

- Which fields are used, and in which order
- Which fields are required
- Which fields need to be uppercased for the actual mailing (to facilitate automated sorting of mail)
- The labels for the administrative area (state, province, parish, etc.), locality (city/post town/district, etc.), dependent locality (neighborhood, suburb, district, etc) and the postal code (postal code or ZIP code)
- The regular expression pattern for validating postal codes

The [country](https://github.com/commerceguys/addressing/blob/master/src/Country/Country.php) provides the following information:

- The country name.
- The numeric and three-letter country codes.
- The official currency code, when known.
- The timezones which the country spans.

The [subdivision](https://github.com/commerceguys/addressing/blob/master/src/Subdivision/Subdivision.php) provides the following information:

- The subdivision code (used to represent the subdivison on a parcel/envelope, e.g. CA for California)
- The subdivison name (shown to the user in a dropdown)
- The local code and name, if the country uses a non-latin script (e.g. Cyrilic in Russia).
- The postal code prefix (used to ensure that a postal code begins with the expected characters)

Subdivisions are hierarchical and can have up to three levels:
Administrative Area -> Locality -> Dependent Locality.

```php
use CommerceGuys\Addressing\AddressFormat\AddressFormatRepository;
use CommerceGuys\Addressing\Country\CountryRepository;
use CommerceGuys\Addressing\Subdivision\SubdivisionRepository;

$countryRepository = new CountryRepository();
$addressFormatRepository = new AddressFormatRepository();
$subdivisionRepository = new SubdivisionRepository();

// Get the country list (countryCode => name), in French.
$countryList = $countryRepository->getList('fr-FR');

// Get the country object for Brazil.
$brazil = $countryRepository->get('BR');
echo $brazil->getThreeLetterCode(); // BRA
echo $brazil->getName(); // Brazil
echo $brazil->getCurrencyCode(); // BRL
print_r($brazil->getTimezones());

// Get all country objects.
$countries = $countryRepository->getAll();

// Get the address format for Brazil.
$addressFormat = $addressFormatRepository->get('BR');

// Get the subdivisions for Brazil.
$states = $subdivisionRepository->getAll(['BR']);
foreach ($states as $state) {
    $municipalities = $state->getChildren();
}

// Get the subdivisions for Brazilian state Ceará.
$municipalities = $subdivisionRepository->getAll(['BR', 'CE']);
foreach ($municipalities as $municipality) {
    echo $municipality->getName();
}
```

# Formatters

Addresses are formatted according to the address format, in HTML or text.

## DefaultFormatter

Formats an address for display, always adds the localized country name.

```php
use CommerceGuys\Addressing\Address;
use CommerceGuys\Addressing\Formatter\DefaultFormatter;
use CommerceGuys\Addressing\AddressFormat\AddressFormatRepository;
use CommerceGuys\Addressing\Country\CountryRepository;
use CommerceGuys\Addressing\Subdivision\SubdivisionRepository;

$addressFormatRepository = new AddressFormatRepository();
$countryRepository = new CountryRepository();
$subdivisionRepository = new SubdivisionRepository();
$formatter = new DefaultFormatter($addressFormatRepository, $countryRepository, $subdivisionRepository);
// Options passed to the constructor or format() allow turning off
// html rendering, customizing the wrapper element and its attributes.

$address = new Address();
$address = $address
    ->withCountryCode('US')
    ->withAdministrativeArea('CA')
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

Takes care of uppercasing fields where required by the format (to facilitate automated mail sorting).

Requires specifying the origin country code, allowing it to differentiate between domestic and international mail.
In case of domestic mail, the country name is not displayed at all.
In case of international mail:

1. The postal code is prefixed with the destination's postal code prefix.
2. The country name is added to the formatted address, in both the current locale and English.
This matches the recommendation given by the Universal Postal Union, to avoid difficulties in countries of transit.

```php
use CommerceGuys\Addressing\Address;
use CommerceGuys\Addressing\Formatter\PostalLabelFormatter;
use CommerceGuys\Addressing\AddressFormat\AddressFormatRepository;
use CommerceGuys\Addressing\Country\CountryRepository;
use CommerceGuys\Addressing\Subdivision\SubdivisionRepository;

$addressFormatRepository = new AddressFormatRepository();
$countryRepository = new CountryRepository();
$subdivisionRepository = new SubdivisionRepository();
// Defaults to text rendering. Requires passing the "origin_country"
// (e.g. 'FR') to the constructor or to format().
$formatter = new PostalLabelFormatter($addressFormatRepository, $countryRepository, $subdivisionRepository, ['locale' => 'fr']);

$address = new Address();
$address = $address
    ->withCountryCode('US')
    ->withAdministrativeArea('CA')
    ->withLocality('Mountain View')
    ->withAddressLine1('1098 Alta Ave');

echo $formatter->format($address, ['origin_country' => 'FR']);

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
use CommerceGuys\Addressing\Address;
use CommerceGuys\Addressing\Validator\Constraints\AddressFormatConstraint;
use CommerceGuys\Addressing\Validator\Constraints\CountryConstraint;
use Symfony\Component\Validator\Validation;

$address = new Address('FR');

$validator = Validation::createValidator();
// Validate the country code, then validate the rest of the address.
$violations = $validator->validate($address->getCountryCode(), new CountryConstraint());
if (!$violations->count()) {
  $violations = $validator->validate($address, new AddressFormatConstraint());
}
```

# Zones

[Zones](https://github.com/commerceguys/addressing/blob/master/src/Zone/Zone.php) are [territorial](https://github.com/commerceguys/addressing/blob/master/src/Zone/ZoneTerritory.php) groupings often used for shipping or tax purposes.
For example, a set of shipping rates associated with a zone where the rates
become available only if the customer's address belongs to the zone.

A zone can match countries, subdivisions (states/provinces/municipalities), postal codes.
Postal codes can also be expressed using ranges or regular expressions.

Examples of zones:
- California and Nevada
- Belgium, Netherlands, Luxemburg
- Germany and a set of Austrian postal codes (6691, 6991, 6992, 6993)
- Austria without specific postal codes (6691, 6991, 6992, 6993)

```php
use CommerceGuys\Addressing\Address;
use CommerceGuys\Addressing\Zone\Zone;

// Create the German VAT zone (Germany and 4 Austrian postal codes).
$zone = new Zone([
    'id' => 'german_vat',
    'label' => 'German VAT',
    'territories' => [
        ['country_code' => 'DE'],
        ['country_code' => 'AT', 'included_postal_codes' => '6691, 6991:6993'],
    ],
]);

// Check if the provided austrian address matches the German VAT zone.
$austrianAddress = new Address();
$austrianAddress = $austrianAddress
    ->withCountryCode('AT')
    ->withPostalCode('6992');
echo $zone->match($austrianAddress); // true
```

# Integrations

- [Drupal module](https://drupal.org/project/address)
