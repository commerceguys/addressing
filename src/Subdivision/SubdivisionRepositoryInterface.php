<?php

namespace CommerceGuys\Addressing\Subdivision;

/**
 * Subdivision repository interface.
 */
interface SubdivisionRepositoryInterface
{
    /**
     * Returns a subdivision instance matching the provided code and parents.
     *
     * @param string $code   The subdivision code.
     * @param array  $parents The parents (country code, subdivision codes).
     *
     * @return Subdivision|null The subdivision instance, if found.
     */
    public function get(string $code, array $parents): ?Subdivision;

    /**
     * Returns all subdivision instances for the provided parents.
     *
     * @param array $parents The parents (country code, subdivision codes).
     *
     * @return Subdivision[] An array of subdivision instances.
     */
    public function getAll(array $parents): array;

    /**
     * Returns a list of subdivisions for the provided parents.
     *
     * @param array  $parents The parents (country code, subdivision codes).
     * @param string|null $locale The locale (e.g. fr-FR).
     *
     * @return array An array of subdivision names, keyed by code.
     */
    public function getList(array $parents, ?string $locale = null): array;
}
