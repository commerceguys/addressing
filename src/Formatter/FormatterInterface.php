<?php

namespace CommerceGuys\Addressing\Formatter;

use CommerceGuys\Addressing\AddressInterface;

interface FormatterInterface
{
    /**
     * Gets the locale.
     *
     * @return string The locale.
     */
    public function getLocale();

    /**
     * Sets the locale.
     *
     * @param string $locale The locale.
     */
    public function setLocale($locale);

    /**
     * Gets the options.
     *
     * @return array $options The options.
     */
    public function getOptions();

    /**
     * Sets the options.
     *
     * @param array $options The options.
     */
    public function setOptions(array $options);

    /**
     * Gets the option with the provided key.
     *
     * @return array $options The options.
     */
    public function getOption($key);

    /**
     * Sets the option with the provided key.
     *
     * @param string $key   The key.
     * @param string $value The new value.
     */
    public function setOption($key, $value);

    /**
     * Formats an address.
     *
     * @param AddressInterface $address The address.
     *
     * @return string The formatted address.
     */
    public function format(AddressInterface $address);
}
