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
     * Returns all available subdivision instances for the provided country code.
     *
     * @param string $countryCode The country code.
     * @param int    $parentId    The parent id.
     * @param string $locale      The locale (e.g. fr-FR).
     *
     * @return Subdivision[] An array of subdivision instances.
     */
    public function getAll($countryCode, $parentId = null, $locale = null);
}
