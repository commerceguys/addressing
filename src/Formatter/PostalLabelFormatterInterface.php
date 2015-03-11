<?php

namespace CommerceGuys\Addressing\Formatter;

interface PostalLabelFormatterInterface extends FormatterInterface
{
    /**
     * Gets the origin country code.
     *
     * @return string The origin country code.
     */
    public function getOriginCountryCode();

    /**
     * Sets the origin country code.
     *
     * @param string $originCountryCode The origin country code.
     */
    public function setOriginCountryCode($originCountryCode);
}
