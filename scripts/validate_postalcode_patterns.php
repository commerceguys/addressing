<?php

include '../vendor/autoload.php';


use CommerceGuys\Addressing\Model\Address;
use CommerceGuys\Addressing\Validator\Constraints\AddressFormat;
use CommerceGuys\Addressing\Validator\Constraints\Country;
use Symfony\Component\Validator\Validation;
use CommerceGuys\Addressing\Repository\CountryRepository;
use CommerceGuys\Addressing\Repository\AddressFormatRepository;

$address = new Address();
$addressFormatRepository = new AddressFormatRepository();


$address
  ->setAddressLine1('Addressline1')
  ->setAddressLine1('Addressline2')
  ->setLocality('Locality');


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

// Loop over countries
foreach ($foundCountries as $countryCode) {
  $definition = file_get_contents('raw/' . $countryCode . '.json');
  $definition = json_decode($definition, TRUE);

  // If country definition has zip examples, check if they pass validation.
  if (isset($definition['zipex'])) {

    $zip_examples = explode(',', $definition['zipex']);
    $address->setCountryCode($countryCode);
    $violations = $validator->validate($address->getCountryCode(), new Country());

    foreach ($zip_examples as $zip_example) {
      $address->setPostalCode($zip_example);
      $violations = $validator->validate($address, new AddressFormat());
      $all_available_zipcodes_for_country = implode('" - "', $zip_examples);
      foreach ($violations as $violation) {
        // We're only interested in postal code pattern errors here.
        if ($violation->getPropertyPath() == '[postalCode]') {
          $message = $violation->getMessage();
          $addressFormat = $addressFormatRepository->get($countryCode);
          $postalCodePattern = $addressFormat->getPostalCodePattern();
          echo "Error for countrycode '$countryCode' with postalcode '$zip_example'.  \nError: $message\nPostal code pattern: $postalCodePattern\nAll postalcodes being tested for this country: $all_available_zipcodes_for_country\n\n";

        }
      }
    }
  }
}
