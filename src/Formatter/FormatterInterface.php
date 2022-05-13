<?php

namespace CommerceGuys\Addressing\Formatter;

use CommerceGuys\Addressing\AddressInterface;

interface FormatterInterface
{
    /**
     * Formats an address.
     *
     * Supported options:
     * - locale (default: 'en'): The locale to use for the country name.
     * - html (default: true): Whether to output HTML.
     * - html_tag (default: 'p'): The wrapper HTML element to use.
     * - html_attributes: The attributes to set on the wrapper HTML element.
     *
     * @param AddressInterface $address The address.
     * @param array            $options The formatting options.
     *
     * @return string The formatted address.
     */
    public function format(AddressInterface $address, array $options = []): string;
}
