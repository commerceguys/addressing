<?php

/**
 * Google's dataset includes regular expressions for validating postal codes.
 * These regular expressions are meant to be consumed by Google's Java library,
 * and compatibility with PHP's preg_match is not 100% guaranteed.
 * This scripts performs validation to ensure compatibility.
 */

include '../vendor/autoload.php';

use CommerceGuys\Addressing\Address;
use CommerceGuys\Addressing\AddressFormat\AddressField;
use CommerceGuys\Addressing\AddressFormat\AddressFormatRepository;
use CommerceGuys\Addressing\Country\CountryRepository;
use CommerceGuys\Addressing\Validator\Constraints\AddressFormatConstraint;
use Symfony\Component\Validator\Validation;

$addressFormatRepository = new AddressFormatRepository();

$address = new Address();
$address = $address
  ->withAddressLine1('Address line1')
  ->withAddressLine1('Address line2')
  ->withLocality('Locality');

$validator = Validation::createValidator();

// Create a list of countries for which Google has definitions.
$foundCountries = ['ZZ'];
$countryRepository = new CountryRepository();
$countries = $countryRepository->getList();
$serviceUrl = 'http://i18napis.appspot.com/address';
$index = file_get_contents($serviceUrl);
foreach ($countries as $countryCode => $countryName) {
  $link = "<a href='/address/data/{$countryCode}'>";
  // This is still faster than running a file_exists() for each country code.
  if (strpos($index, $link) !== FALSE) {
    $foundCountries[] = $countryCode;
  }
}

foreach ($foundCountries as $countryCode) {
  $addressFormat = $addressFormatRepository->get($countryCode);
  if (!in_array(AddressField::POSTAL_CODE, $addressFormat->getUsedFields())) {
    continue;
  }

  $definition = file_get_contents('raw/' . $countryCode . '.json');
  $definition = json_decode($definition, TRUE);
  // If country definition has zip examples, check if they pass validation.
  if (isset($definition['zipex'])) {
    $zipExamples = explode(',', $definition['zipex']);
    $address = $address->withCountryCode($countryCode);

    foreach ($zipExamples as $zipExample) {
      if (strpos($zipExample, ':') !== FALSE) {
        // Ignore ranges for now, the non-range examples are enough.
        continue;
      }

      $address = $address->withPostalCode($zipExample);
      $violations = $validator->validate($address, new AddressFormatConstraint());
      $formattedExamples = implode(', ', $zipExamples);
      foreach ($violations as $violation) {
        if ($violation->getPropertyPath() == '[postalCode]') {
          $message = $violation->getMessage();
          $postalCodePattern = $addressFormat->getPostalCodePattern();
          echo "Error for countrycode '$countryCode' with postal code '$zipExample'.\n";
          echo "Error: $message\n";
          echo "Postal code pattern: $postalCodePattern\n";
          echo "All available postal code examples: $formattedExamples\n\n";

          // Once we catch an error in a country, don't try other examples.
          continue 3;
        }
      }
    }
  }
}
