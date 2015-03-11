<?php

namespace CommerceGuys\Addressing\Formatter;

use CommerceGuys\Addressing\Enum\AddressField;
use CommerceGuys\Addressing\Model\AddressInterface;
use CommerceGuys\Addressing\Model\AddressFormatInterface;
use CommerceGuys\Addressing\Provider\DataProviderInterface;

/**
 * Formats an address for display.
 *
 * The address is formatted according to the destination country format.
 * The localized country name is added to the formatted address.
 */
class DefaultFormatter implements FormatterInterface
{
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
     * The data provider.
     *
     * @var DataProviderInterface
     */
    protected $dataProvider;

    /**
     * Creates a FormatterBase instance.
     *
     * @param DataProviderInterface $dataProvider The data provider.
     * @param string                $locale       The current locale.
     * @param array                 $options      The options.
     */
    public function __construct(DataProviderInterface $dataProvider, $locale = null, array $options = [])
    {
        $this->dataProvider = $dataProvider;
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
        $addressFormat = $this->dataProvider->getAddressFormat($countryCode, $this->locale);
        $formatString = $addressFormat->getFormat();

        $view = $this->buildView($address, $addressFormat);
        $view = $this->renderView($view);
        // Remove the rendered replacements from the view and place them
        // into the format string.
        $replacements = [];
        foreach ($view as $key => $element) {
            if (substr($key, 0, 1) == '%') {
                $replacements[$key] = $element;
                unset($view[$key]);
            }
        }
        $view['address'] = strtr($formatString, $replacements);
        $output = implode("\n", $view);
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
        $values = $this->getValues($address);
        // Filter out unused fields.
        $usedFields = $addressFormat->getUsedFields();
        $values = array_intersect_key($values, array_combine($usedFields, $usedFields));

        $weight = 1;
        $view = [
            // Placeholder that receives the format string with the tokens
            // replaced by the rendered '%' elements.
            'address' => [
                'value' => '',
                'weight' => $weight++,
            ],
        ];
        foreach ($values as $field => $value) {
            // The constant is more suitable as a class than the value since
            // it's snake_case and not camelCase.
            $class = str_replace('_', '-', strtolower(AddressField::getKey($field)));
            $view['%' . $field] = [
                'html_tag' => 'span',
                'html_attributes' => ['class' => $class],
                'value' => $value,
                'weight' => $weight++,
            ];
        }
        // The localized country name.
        $view['country'] = [
            'html_tag' => 'span',
            'html_attributes' => ['class' => 'country'],
            'value' => $this->dataProvider->getCountryName($address->getCountryCode(), $this->locale),
            'weight' => 50,
        ];
        // Move the country element to the beginning of the array if the
        // address format is major-to-minor.
        $formatString = $addressFormat->getFormat();
        if (strpos($formatString, AddressField::ADDRESS_LINE2) < strpos($formatString, AddressField::ADDRESS_LINE1)) {
            $view['country']['weight'] = -50;
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
        // Sort the elements by weight.
        uasort($view, array(get_class($this), 'sortByWeight'));

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
     * uasort callback: Sorts the elements by the weight property.
     */
    public static function sortByWeight($a, $b)
    {
        $a_weight = (is_array($a) && isset($a['weight'])) ? $a['weight'] : 0;
        $b_weight = (is_array($b) && isset($b['weight'])) ? $b['weight'] : 0;

        if ($a_weight == $b_weight) {
          return 0;
        }

        return ($a_weight < $b_weight) ? -1 : 1;
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
     * @param AddressInterface $address The address.
     *
     * @return array The values, keyed by address field.
     */
    protected function getValues(AddressInterface $address)
    {
        $values = [];
        foreach (AddressField::getAll() as $field) {
            $getter = 'get' . ucfirst($field);
            $values[$field] = $address->$getter();
        }

        // Replace the subdivision values with the names of any predefined ones.
        $subdivisionFields = [
            AddressField::ADMINISTRATIVE_AREA,
            AddressField::LOCALITY,
            AddressField::DEPENDENT_LOCALITY,
        ];
        foreach ($subdivisionFields as $field) {
            if (empty($values[$field])) {
                // This level is empty, so there can be no sublevels.
                break;
            }
            $subdivision = $this->dataProvider->getSubdivision($values[$field], $address->getLocale());
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
