This component stores and manipulates postal addresses, more precisely the kind
of postal addresses meant to identify a precise recipient location for shipping
purposes.

The storage of exact geographic coordinates and the identification non-postal
geographic features (countries, regions, mountains, lakes, etc.) is explicitly
out of scope.

# Data model

The data model of postal address data is based on a tree of features:

* Country
* Administrative area
* Locality
* Dependent Locality
* ZIP / Postal code
* Sorting code
* Address line 1
* Address line 2
* Address line 3
* Organization
* Recipient

# Formatting and validation metadata

The validation of addresses is based on a tree of validation data, up to three
levels:

* Country
* Administrative area
* Locality
* Dependent Locality

Each level specify:

* Address format, based on placeholders of the fields of the data model;
* The labels for the administrative area (state, province, parish, etc.), and
  the postal code (postal code or ZIP code);
* Validation of postal codes (via regular expression patterns);
* Uppercasing requirements (many postoffice in the world mandate some lines of the address to be uppercased to make optical reading easier);
* Examples of postal code;
* etc.

The default metadata shipped with this component is based on the one build by
Google for Android (licensed under Apache 2).
