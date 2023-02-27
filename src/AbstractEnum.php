<?php

namespace CommerceGuys\Addressing;

/**
 * Base class for enumerations.
 */
abstract class AbstractEnum
{
    /**
     * Static cache of available values, shared with all subclasses.
     */
    protected static array $values = [];

    private function __construct()
    {
    }

    /**
     * Gets all available values.
     *
     * @return array The available values, keyed by constant.
     * @throws \ReflectionException
     */
    public static function getAll(): array
    {
        $class = static::class;
        if (!isset(static::$values[$class])) {
            $reflection = new \ReflectionClass($class);
            static::$values[$class] = $reflection->getConstants();
        }

        return static::$values[$class];
    }

    /**
     * Gets the key of the provided value.
     *
     * @return string|false The key if found, false otherwise.
     * @throws \ReflectionException
     * @throws \ReflectionException
     */
    public static function getKey(string $value): bool|string
    {
        return array_search($value, static::getAll(), true);
    }

    /**
     * Checks whether the provided value is defined.
     *
     * @return bool True if the value is defined, false otherwise.
     * @throws \ReflectionException
     * @throws \ReflectionException
     */
    public static function exists(string $value): bool
    {
        return in_array($value, static::getAll(), true);
    }

    /**
     * Asserts that the provided value is defined.
     *
     * @throws \InvalidArgumentException
     * @throws \ReflectionException
     */
    public static function assertExists(string $value): void
    {
        if (!static::exists($value)) {
            $class = substr(strrchr(static::class, '\\'), 1);
            throw new \InvalidArgumentException(sprintf('"%s" is not a valid %s value.', $value, $class));
        }
    }

    /**
     * Asserts that all provided values are defined.
     * @throws \ReflectionException
     */
    public static function assertAllExist(array $values): void
    {
        foreach ($values as $value) {
            static::assertExists($value);
        }
    }
}
