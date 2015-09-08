<?php

namespace CommerceGuys\Addressing\Model;

/**
 * Interface for country subdivisons.
 *
 * Subdivisions are hierarchical and can have up to three levels:
 * Administrative Area -> Locality -> Dependent Locality.
 */
interface SubdivisionInterface
{
    /**
     * Gets the subdivision parent.
     *
     * @return SubdivisionInterface|null The parent, or NULL if there is none.
     */
    public function getParent();

    /**
     * Gets the two-letter country code.
     *
     * This is a CLDR country code, since CLDR includes additional countries
     * for addressing purposes, such as Canary Islands (IC).
     *
     * @return string The two-letter country code.
     */
    public function getCountryCode();

    /**
     * Gets the subdivision id.
     *
     * @return string The subdivision id.
     */
    public function getId();

    /**
     * Gets the subdivision code.
     *
     * Represents the subdivision on the formatted address.
     * For example: "CA" for California.
     *
     * The code will be in the local (non-latin) script if the country uses one.
     *
     * @return string The subdivision code.
     */
    public function getCode();

    /**
     * Gets the subdivision name.
     *
     * @return string The subdivision name.
     */
    public function getName();

    /**
     * Gets the postal code pattern.
     *
     * This is a regular expression pattern used to validate postal codes.
     *
     * @return string|null The postal code pattern.
     */
    public function getPostalCodePattern();

    /**
     * Gets the postal code pattern type.
     *
     * @return string|null The postal code pattern type.
     */
    public function getPostalCodePatternType();

    /**
     * Gets the subdivision children.
     *
     * @return SubdivisionInterface[] The subdivision children.
     */
    public function getChildren();

    /**
     * Checks whether the subdivision has children.
     *
     * @return bool TRUE if the subdivision has children, FALSE otherwise.
     */
    public function hasChildren();
}
