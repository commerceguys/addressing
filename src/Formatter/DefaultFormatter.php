<?php

namespace CommerceGuys\Addressing\Formatter;

use CommerceGuys\Addressing\AddressInterface;
use CommerceGuys\Addressing\AddressFormat\AddressField;
use CommerceGuys\Addressing\AddressFormat\AddressFormat;
use CommerceGuys\Addressing\AddressFormat\AddressFormatRepositoryInterface;
use CommerceGuys\Addressing\Country\CountryRepositoryInterface;
use CommerceGuys\Addressing\Locale;
use CommerceGuys\Addressing\Subdivision\SubdivisionRepositoryInterface;

/**
 * Formats an address for display.
 *
 * The address is formatted according to the destination country format.
 * The localized country name is added to the formatted address.
 */
class DefaultFormatter implements FormatterInterface
{
    /**
     * The address format repository.
     *
     * @var AddressFormatRepositoryInterface
     */
    protected AddressFormatRepositoryInterface $addressFormatRepository;

    /**
     * The country repository.
     *
     * @var CountryRepositoryInterface
     */
    protected CountryRepositoryInterface $countryRepository;

    /**
     * The subdivision repository.
     *
     * @var SubdivisionRepositoryInterface
     */
    protected SubdivisionRepositoryInterface $subdivisionRepository;

    /**
     * The default options.
     *
     * @var array
     */
    protected array $defaultOptions = [
        'locale' => 'en',
        'html' => true,
        'html_tag' => 'p',
        'html_attributes' => ['translate' => 'no'],
    ];

    /**
     * Creates a DefaultFormatter instance.
     *
     * @param AddressFormatRepositoryInterface $addressFormatRepository
     * @param CountryRepositoryInterface       $countryRepository
     * @param SubdivisionRepositoryInterface   $subdivisionRepository
     * @param array                            $defaultOptions
     */
    public function __construct(AddressFormatRepositoryInterface $addressFormatRepository, CountryRepositoryInterface $countryRepository, SubdivisionRepositoryInterface $subdivisionRepository, array $defaultOptions = [])
    {
        $this->validateOptions($defaultOptions);
        $this->addressFormatRepository = $addressFormatRepository;
        $this->countryRepository = $countryRepository;
        $this->subdivisionRepository = $subdivisionRepository;
        $this->defaultOptions = array_replace($this->defaultOptions, $defaultOptions);
    }

    /**
     * {@inheritdoc}
     * @throws \ReflectionException
     */
    public function format(AddressInterface $address, array $options = []): string
    {
        $this->validateOptions($options);
        $options = array_replace($this->defaultOptions, $options);
        $countryCode = $address->getCountryCode();
        $addressFormat = $this->addressFormatRepository->get($countryCode);
        // Add the country to the bottom or the top of the format string,
        // depending on whether the format is minor-to-major or major-to-minor.
        if (Locale::matchCandidates($addressFormat->getLocale(), $address->getLocale())) {
            $formatString = '%country' . "\n" . $addressFormat->getLocalFormat();
        } else {
            $formatString = $addressFormat->getFormat() . "\n" . '%country';
        }

        $view = $this->buildView($address, $addressFormat, $options);
        $view = $this->renderView($view);
        // Insert the rendered elements into the format string.
        $replacements = [];
        foreach ($view as $key => $element) {
            $replacements['%' . $key] = $element;
        }
        $output = strtr($formatString, $replacements);
        $output = $this->cleanupOutput($output);

        if (!empty($options['html'])) {
            $output = nl2br($output, false);
            // Add the HTML wrapper element.
            $attributes = $this->renderAttributes($options['html_attributes']);
            $prefix = '<' . $options['html_tag'] . ' ' . $attributes . '>' . "\n";
            $suffix = "\n" . '</' . $options['html_tag'] . '>';
            $output = $prefix . $output . $suffix;
        }

        return $output;
    }

    /**
     * Validates the provided options.
     *
     * Ensures the absence of unknown keys, correct data types and values.
     *
     * @throws \InvalidArgumentException
     */
    protected function validateOptions(array $options): void
    {
        foreach ($options as $option => $value) {
            if (!array_key_exists($option, $this->defaultOptions)) {
                throw new \InvalidArgumentException(sprintf('Unrecognized option "%s".', $option));
            }
        }
        if (isset($options['html']) && !is_bool($options['html'])) {
            throw new \InvalidArgumentException('The option "html" must be a boolean.');
        }
        if (isset($options['html_attributes']) && !is_array($options['html_attributes'])) {
            throw new \InvalidArgumentException('The option "html_attributes" must be an array.');
        }
    }

    /**
     * Builds the view for the given address.
     *
     * @param AddressInterface $address The address.
     * @param AddressFormat $addressFormat The address format.
     * @param array $options The formatting options.
     *
     * @return array The view.
     * @throws \ReflectionException
     * @throws \ReflectionException
     */
    protected function buildView(AddressInterface $address, AddressFormat $addressFormat, array $options): array
    {
        $countries = $this->countryRepository->getList($options['locale']);
        $values = $this->getValues($address, $addressFormat);
        $countryCode = $address->getCountryCode();

        $view = [];
        $view['country'] = [
            'html' => $options['html'],
            'html_tag' => 'span',
            'html_attributes' => ['class' => 'country'],
            'value' => $countries[$countryCode] ?? $countryCode,
        ];
        foreach ($addressFormat->getUsedFields() as $field) {
            // The constant is more suitable as a class than the value since
            // it's snake_case and not camelCase.
            $class = str_replace('_', '-', strtolower(AddressField::getKey($field)));
            $view[$field] = [
                'html' => $options['html'],
                'html_tag' => 'span',
                'html_attributes' => ['class' => $class],
                'value' => $values[$field],
            ];
        }

        return $view;
    }

    /**
     * Renders the given view.
     *
     * @param array $view The view.
     *
     * @return array An array of rendered values with the original keys preserved.
     */
    protected function renderView(array $view): array
    {
        foreach ($view as $key => $element) {
            if (empty($element['value'])) {
                $view[$key] = '';
                continue;
            }

            if (!empty($element['html'])) {
                $attributes = $this->renderAttributes($element['html_attributes']);
                $prefix = '<' . $element['html_tag'] . ' ' . $attributes . '>';
                $suffix = '</' . $element['html_tag'] . '>';
                $value = htmlspecialchars($element['value'], ENT_QUOTES, 'UTF-8');
                $view[$key] = $prefix . $value . $suffix;
            } else {
                $view[$key] = strip_tags($element['value']);
            }
        }

        return $view;
    }

    protected function renderAttributes(array $attributes): string
    {
        foreach ($attributes as $name => $value) {
            if (is_array($value)) {
                $value = implode(' ', $value);
            }
            $attributes[$name] = $name . '="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '"';
        }

        return implode(' ', $attributes);
    }

    /**
     * Removes empty lines, leading/trailing punctuation, excess whitespace.
     */
    protected function cleanupOutput(string $output): string
    {
        $lines = explode("\n", $output);
        foreach ($lines as $index => $line) {
            $line = trim($line, ' -,');
            $line = preg_replace('/\s\s+/', ' ', $line);
            $lines[$index] = $line;
        }
        // Remove empty lines.
        $lines = array_filter($lines);

        return implode("\n", $lines);
    }

    /**
     * Gets the address values used to build the view.
     *
     * @return array The values, keyed by address field.
     * @throws \ReflectionException
     */
    protected function getValues(AddressInterface $address, AddressFormat $addressFormat): array
    {
        $values = [];
        foreach (AddressField::getAll() as $field) {
            $getter = 'get' . ucfirst($field);
            $values[$field] = $address->$getter();
        }

        // Replace the subdivision values with the names of any predefined ones.
        $originalValues = [];
        $subdivisionFields = $addressFormat->getUsedSubdivisionFields();
        $parents = [];
        foreach ($subdivisionFields as $index => $field) {
            if (empty($values[$field])) {
                // This level is empty, so there can be no sublevels.
                break;
            }
            $parents[] = $index ? $originalValues[$subdivisionFields[$index - 1]] : $address->getCountryCode();
            $subdivision = $this->subdivisionRepository->get($values[$field], $parents);
            if (!$subdivision) {
                break;
            }

            // Remember the original value so that it can be used for $parents.
            $originalValues[$field] = $values[$field];
            // Replace the value with the expected code.
            $useLocalName = Locale::matchCandidates($address->getLocale(), $subdivision->getLocale());
            $values[$field] = $useLocalName ? $subdivision->getLocalCode() : $subdivision->getCode();
            if (!$subdivision->hasChildren()) {
                // The current subdivision has no children, stop.
                break;
            }
        }

        return $values;
    }
}
