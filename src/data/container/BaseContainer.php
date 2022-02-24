<?php

declare(strict_types=1);

namespace joole\framework\data\container;

use joole\framework\data\container\object\ContainerObject;
use Psr\Container\ContainerInterface;

/**
 * BaseContainer implements PSR's ContainerInterface and boosts it.
 *
 * @noinspection PhpUnused
 */
abstract class BaseContainer implements ContainerInterface
{

    /**
     * Preloaded objects that can be found by the name YourClass::class
     *
     * @var ContainerObject[] = [
     *     "\My\Example\Class" => object(\My\Example\Class)
     * ]
     */
    private static array $instances = [];

    /**
     * Returns object of container or throws exception.
     *
     * @param string $id
     * @return ContainerObject
     * @throws NotFoundException
     */
    public function get(string $id): ContainerObject
    {
        // Container's objects
        $instances = &static::$instances;

        // If container with given $id doesn't exists
        if (!isset($instances[$id])) {
            throw new NotFoundException('Cannot find container with id ' . $id . '!');
        }

        return $instances[$id];
    }

    /**
     * Checks for the presence of an object with the received id.
     *
     * @param string $id
     * @return bool
     */
    public function has(string $id): bool
    {
        return isset(self::$instances);
    }

    /**
     * Registers objects to container.
     *
     * @param ContainerObject $object
     * @param ContainerObject[] $params - Classes of registered containers.
     * @return void
     */
    abstract public function register(ContainerObject $object, ContainerObject... $params): void;

}