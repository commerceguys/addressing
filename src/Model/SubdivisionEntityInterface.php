<?php

namespace CommerceGuys\Addressing\Model;

use Doctrine\Common\Collections\Collection;

interface SubdivisionEntityInterface extends SubdivisionInterface
{
    /**
     * Sets the subdivision parent.
     *
     * @param SubdivisionEntityInterface|null $parent The subdivision parent.
     *
     * @return self
     */
    public function setParent(SubdivisionEntityInterface $parent = null);

    /**
     * Sets the two-letter country code.
     *
     * @param string $countryCode The two-letter country code.
     *
     * @return self
     */
    public function setCountryCode($countryCode);

    /**
     * Sets the subdivision id.
     *
     * @param string $id The subdivision id.
     *
     * @return self
     */
    public function setId($id);

    /**
     * Sets the subdivision code.
     *
     * @param string $code The subdivision code.
     *
     * @return self
     */
    public function setCode($code);

    /**
     * Sets the subdivision name.
     *
     * @param string $name The subdivision name.
     *
     * @return self
     */
    public function setName($name);

    /**
     * Sets the postal code pattern.
     *
     * @param string $postalCodePattern The postal code pattern.
     *
     * @return self
     */
    public function setPostalCodePattern($postalCodePattern);

    /**
     * Sets the postal code pattern type.
     *
     * @param string $postalCodePatternType The postal code pattern type.
     *
     * @return self
     */
    public function setPostalCodePatternType($postalCodePatternType);

    /**
     * Sets the subdivision children.
     *
     * @param SubdivisionEntityInterface[] $children The subdivision children.
     *
     * @return self
     */
    public function setChildren(Collection $children);
}
