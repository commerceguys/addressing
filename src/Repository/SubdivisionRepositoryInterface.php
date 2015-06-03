<?php

namespace CommerceGuys\Addressing\Repository;

use CommerceGuys\Addressing\Model\Subdivision;

/**
 * Subdivision repository interface.
 */
interface SubdivisionRepositoryInterface
{
    /**
     * Returns a subdivision instance matching the provided id.
     *
     * @param string $id     The subdivision id.
     * @param string $locale The locale (e.g. fr-FR).
     *
     * @return Subdivision|null The subdivision instance, if found.
     */
    public function get($id, $locale = null);

    /**
     * Returns all subdivision instances for the provided country code.
     *
     * @param string $countryCode The country code.
     * @param int    $parentId    The parent id.
     * @param string $locale      The locale (e.g. fr-FR).
     *
     * @return Subdivision[] An array of subdivision instances.
     */
    public function getAll($countryCode, $parentId = null, $locale = null);

    /**
     * Returns a list of subdivisions.
     *
     * @param string $countryCode The country code.
     * @param int    $parentId    The parent id.
     * @param string $locale      The locale (e.g. fr-FR).
     *
     * @return array An array of subdivision names, keyed by id.
     */
    public function getList($countryCode, $parentId = null, $locale = null);

    /**
     * Returns the subdivision depth for the provided country code.
     *
     * Note that a country might use a subdivision field without having
     * predefined subdivisions for it.
     * For example, if the locality field is used by the address format, but
     * the subdivision depth is 1, that means that the field element should be
     * rendered as a textbox, since there's no known data to put in a dropdown.
     *
     * It is also possible to have no subdivisions for a specific parent, even
     * though the country generally has predefined subdivisions at that depth.
     *
     * @return int The subdivision depth. Possible values:
     *             0: no subdivisions have been predefined.
     *             1: administrative areas.
     *             2: administrative areas, localities.
     *             3: administrative areas, localities, dependent localities.
     */
    public function getDepth($countryCode);
}
