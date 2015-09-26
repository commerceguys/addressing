<?php

namespace CommerceGuys\Addressing\Formatter;

use CommerceGuys\Addressing\Enum\AddressField;
use CommerceGuys\Addressing\Model\AddressInterface;
use CommerceGuys\Addressing\Model\AddressFormatInterface;
use CommerceGuys\Addressing\Repository\AddressFormatRepositoryInterface;
use CommerceGuys\Addressing\Repository\CountryRepositoryInterface;
use CommerceGuys\Addressing\Repository\SubdivisionRepositoryInterface;

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
    protected $addressFormatRepository;

    /**
     * The country repository.
     *
     * @var CountryRepositoryInterface
     */
    protected $countryRepository;

    /**
     * The subdivision repository.
     *
     * @var SubdivisionRepositoryInterface
     */
    protected $subdivisionRepository;

    /**
     * The locale.
     *
     * @var string
     */
    protected $locale;

    /**
     * The options.
     *
     * @var array
     */
    protected $options = [];

    /**
     * Creates a DefaultFormatter instance.
     *
     * @param AddressFormatRepositoryInterface $addressFormatRepository
     * @param CountryRepositoryInterface       $countryRepository
     * @param SubdivisionRepositoryInterface   $subdivisionRepository
     * @param string                           $locale
     * @param array                            $options
     */
    public function __construct(AddressFormatRepositoryInterface $addressFormatRepository, CountryRepositoryInterface $countryRepository, SubdivisionRepositoryInterface $subdivisionRepository, $locale = null, array $options = [])
    {
        $this->addressFormatRepository = $addressFormatRepository;
        $this->countryRepository = $countryRepository;
        $this->subdivisionRepository = $subdivisionRepository;
        $this->locale = $locale;
        $this->setOptions($options);
    }

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
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * {@inheritdoc}
     */
    public function setOptions(array $options)
    {
        $this->options = $options + $this->getDefaultOptions();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOption($key)
    {
        return array_key_exists($key, $this->options) ? $this->options[$key] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function setOption($key, $value)
    {
        if (!array_key_exists($key, $this->getDefaultOptions())) {
            throw new \InvalidArgumentException(sprintf('Invalid option "%s".', $key));
        }
        $this->options[$key] = $value;

        return $this;
    }

    /**
     * Gets the default options.
     *
     * @return array The default options.
     */
    protected function getDefaultOptions()
    {
        return [
            'html' => true,
            'html_tag' => 'p',
            'html_attributes' => ['translate' => 'no'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function format(AddressInterface $address)
    {
        $countryCode = $address->getCountryCode();
        $addressFormat = $this->addressFormatRepository->get($countryCode, $address->getLocale());
        $formatString = $addressFormat->getFormat();
        // Add the country to the bottom or the top of the format string,
        // depending on whether the format is minor-to-major or major-to-minor.
        if (strpos($formatString, AddressField::ADDRESS_LINE1) < strpos($formatString, AddressField::ADDRESS_LINE2)) {
            $formatString .= "\n" . '%country';
        } else {
            $formatString = '%country' . "\n" . $formatString;
        }

        $view = $this->buildView($address, $addressFormat);
        $view = $this->renderView($view);
        // Insert the rendered elements into the format string.
        $replacements = [];
        foreach ($view as $key => $element) {
            $replacements['%' . $key] = $element;
        }
        $output = strtr($formatString, $replacements);
        $output = $this->cleanupOutput($output);

        if (!empty($this->options['html'])) {
            $output = nl2br($output, false);
            // Add the HTML wrapper element.
            $attributes = $this->renderAttributes($this->options['html_attributes']);
            $prefix = '<' . $this->options['html_tag'] . ' ' . $attributes . '>' . "\n";
            $suffix = "\n" . '</' . $this->options['html_tag'] . '>';
            $output = $prefix . $output . $suffix;
        }

        return $output;
    }

    /**
     * Builds the view for the given address.
     *
     * @param AddressInterface       $address       The address.
     * @param AddressFormatInterface $addressFormat The address format.
     *
     * @return array The view.
     */
    protected function buildView(AddressInterface $address, AddressFormatInterface $addressFormat)
    {
        $countries = $this->countryRepository->getList($this->locale);
        $values = $this->getValues($address, $addressFormat);
        $view = [];
        $view['country'] = [
            'html_tag' => 'span',
            'html_attributes' => ['class' => 'country'],
            'value' => $countries[$address->getCountryCode()],
        ];
        foreach ($addressFormat->getUsedFields() as $field) {
            // The constant is more suitable as a class than the value since
            // it's snake_case and not camelCase.
            $class = str_replace('_', '-', strtolower(AddressField::getKey($field)));
            $view[$field] = [
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
    protected function renderView(array $view)
    {
        foreach ($view as $key => $element) {
            if (empty($element['value'])) {
                $view[$key] = '';
                continue;
            }

            if (!empty($this->options['html'])) {
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

    /**
     * Renders the given attributes.
     *
     * @param array $attributes The attributes.
     *
     * @return string The rendered attributes.
     */
    protected function renderAttributes(array $attributes)
    {
        foreach ($attributes as $name => $value) {
            if (is_array($value)) {
                $value = implode(' ', (array) $value);
            }
            $attributes[$name] = $name . '="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '"';
        }

        return implode(' ', $attributes);
    }

    /**
     * Removes empty lines, leading punctuation, excess whitespace.
     *
     * @param string $output The output that needs cleanup.
     *
     * @return string The cleaned up output.
     */
    protected function cleanupOutput($output)
    {
        $lines = explode("\n", $output);
        foreach ($lines as $index => $line) {
            $line = trim(preg_replace('/^[-,]+/', '', $line, 1));
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
     * @param AddressInterface       $address       The address.
     * @param AddressFormatInterface $addressFormat The address format.
     *
     * @return array The values, keyed by address field.
     */
    protected function getValues(AddressInterface $address, AddressFormatInterface $addressFormat)
    {
        $values = [];
        foreach (AddressField::getAll() as $field) {
            $getter = 'get' . ucfirst($field);
            $values[$field] = $address->$getter();
        }

        // Replace the subdivision values with the names of any predefined ones.
        foreach ($addressFormat->getUsedSubdivisionFields() as $field) {
            if (empty($values[$field])) {
                // This level is empty, so there can be no sublevels.
                break;
            }
            $subdivision = $this->subdivisionRepository->get($values[$field], $address->getLocale());
            if (!$subdivision) {
                // This level has no predefined subdivisions, stop.
                break;
            }

            $values[$field] = $subdivision->getCode();
            if (!$subdivision->hasChildren()) {
                // The current subdivision has no children, stop.
                break;
            }
        }

        return $values;
    }
}
