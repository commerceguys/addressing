addressing
==========

[![Build Status](https://travis-ci.org/commerceguys/addressing.svg?branch=master)](https://travis-ci.org/commerceguys/addressing)

A PHP 5.4+ addressing library, powered by Google's dataset.

Stores and manipulates postal addresses, meant to identify a precise recipient location for shipping or billing purposes.

Features:
- Address formats for 200 countries
- Subdivisions (administrative areas, localities, dependent localities) for 40 countries
- Subdivision translations for all of the parent country's (i.e Canada, Switzerland) official languages.
- Validation (via Symfony Validator)
- Form generation (via Symfony Form)
- Postal formatting

The dataset is [stored locally](https://github.com/commerceguys/addressing/tree/master/resources) in JSON format, [generated](https://github.com/commerceguys/addressing/blob/master/scripts/generate.php) from Google's [Address Data Service](https://i18napis.appspot.com/address).

The CLDR country list is used (via [commerceguys/intl](https://github.com/commerceguys/intl)), because it includes additional countries for addressing purposes, such as Canary Islands (IC).

Further backstory can be found in [this blog post](https://drupalcommerce.org/blog/16864/commerce-2x-stories-addressing).

# Data model

The [address object](https://github.com/commerceguys/addressing/blob/master/src/Model/AddressInterface.php) represents a postal adddress, and has the following fields:

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

The [address format object](https://github.com/commerceguys/addressing/blob/master/src/Model/AddressFormatInterface.php) contains the following data for a country:

- Which fields are used, and in which order
- Which fields are required
- Which fields need to be uppercased for the actual mailing (to facilitate automated sorting of mail)
- The labels for the administrative area (state, province, parish, etc.), and the postal code (postal code or ZIP code)
- The regular expression pattern for validating postal codes

The [subdivision object](https://github.com/commerceguys/addressing/blob/master/src/Model/SubdivisionInterface.php) contains the following data:

- The subdivision code (used to represent the subdivison on a parcel/envelope, i.e CA for California)
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

# Postal formatter

The address is formatted according to the destination country format.
If the parcel is being sent to a different country, the country name is appended
in the local language (so that the local post office can understand it).

If a country (i.e China/Japan/Korea) uses both major-to-minor (country first) and
minor-to-major (recipient first) address formats, the right one is selected based on the origin address.
For example, if the parcel is being sent from China to China, the local major-to-minor format is used.
But if the parcel is being sent from France to China, then the minor-to-major format is used,
increasing the chances of the address being interpreted correctly.

```php
use CommerceGuys\Addressing\Formatter\PostalFormatter;
use CommerceGuys\Addressing\Metadata\AddressMetadataRepository;

$repository = new AddressMetadataRepository();
$formatter = new PostalFormatter($repository);

// Format an address for sending from Switzerland, in French.
// If the address destination is not Switzerland, the country name will be
// appended in French, uppercase.
echo $formatter->format($address, 'CH', 'fr');
```

# Validator

Address validation relies on the [Symfony Validator](https://github.com/symfony/validator) library.

Checks performed:
- Country code is valid.
- All required fields are filled in.
- All fields unused by the country's format are empty.
- All subdivisions are valid (values matched against predefined subdivisions).
- The postal code is valid (country and subdivision-level patterns).
