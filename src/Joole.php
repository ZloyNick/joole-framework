<?php

declare(strict_types=1);

namespace joole\framework;

use joole\framework\component\BaseComponent;
use joole\framework\component\ComponentInterface;
use joole\framework\data\container\BaseContainer;
use joole\framework\data\container\Container;
use joole\framework\data\container\NotFoundException;
use joole\framework\data\types\ImmutableArray;
use joole\framework\exception\config\ConfigurationException;
use joole\reflector\Reflector;
use RuntimeException;
use function array_keys;
use function class_exists;
use function container;
use function is_array;
use function is_subclass_of;


/**
 * Main Framework's class
 */
final class Joole
{

    /**
     * Containers.
     *
     * @var ImmutableArray
     */
    public static ImmutableArray $containers;

    /**
     * Components.
     *
     * @var ImmutableArray
     */
    public static ImmutableArray $components;

    /**
     * An application's instance.
     *
     * DO NOT USE Jool::$app = ...
     * @see Joole::build()
     *
     * @var Application
     */
    public static Application $app;

    /**
     * Creates application from given class.
     *
     * @param Application $application
     * @return Application
     * @throws ConfigurationException
     */
    public static function build(Application $application): Application
    {
        self::$app = &$application;

        $application->init();
        self::init($application->getConfig('joole'));

        return self::$app;
    }

    /**
     * Returns container or null.
     *
     * @param string|null $id
     * @return \joole\framework\data\container\BaseContainer|null Returns first container or null
     */
    public static function getContainer(null|string $id = null): null|BaseContainer
    {
        $containers = self::$containers;

        if ($id) {
            return self::$containers[$id] ?? null;
        }

        $containersNames = $containers->keys();

        return isset($containersNames[0]) ? $containers[$containersNames[0]] : null;
    }

    /**
     * Returns component by id.
     *
     * @param string $id
     * @return \joole\framework\component\ComponentInterface|null
     */
    public static function getComponent(string $id): null|ComponentInterface
    {
        return self::$components[$id] ?? null;
    }

    /**
     * Initialize Joole Framework
     */
    private static function init(array $data): void
    {
        if (isset($data['containers'])) {
            self::$containers = new ImmutableArray();
            self::registerContainers($data['containers']);
        }

        if (isset($data['components'])) {
            self::registerComponents($data['components']);
        }
    }

    /**
     * Registers components.
     *
     * @param array $components
     */
    private static function registerComponents(array $components): void
    {
        foreach ($components as $num => $component) {
            if (!isset($component['class'])) {
                throw new ConfigurationException(
                    'The "class" index of the component "#[' . $num . ']" is not detected!'
                );
            }

            $class = $component['class'];

            if (!class_exists($class)) {
                throw new RuntimeException('Class "' . $class . '" doesn\'t exists!');
            }

            if (!is_subclass_of($class, BaseComponent::class)) {
                throw new ConfigurationException('Component "' . $class . '" must be instance of ' . BaseComponent::class);
            }

            $name = $component['name'] ?? null;
            $options = $component['options'] ?? [];

            self::$app->registerComponent(new $class($name), $options);
        }
    }

    /**
     * Registers containers.
     *
     * @param array $containers
     * @throws ConfigurationException
     * @throws NotFoundException
     * @throws \ErrorException
     * @throws \ReflectionException
     */
    private static function registerContainers(array $containers): void
    {
        if (!class_exists('joole\reflector\Reflector')) {
            //TODO: debug
            return;
        }

        $reflector = new Reflector();
        $registeredContainers = self::$containers;
        $reflectedContainers = $reflector->buildFromObject($registeredContainers);
        $containerNames = array_keys($containers);

        // Preparing containers before registering
        foreach ($containerNames as $name) {
            $containerNames[$name] = new Container();
        }

        // Creating containers
        $reflectedContainers->getProperty('items')->setValue($containerNames);

        foreach ($containers as $containerName => $containerData) {
            /** @var Container $container */
            $container = $registeredContainers[$containerName];

            foreach ($containerData as $objectArray) {
                $params = $objectArray['params'] ?? [];

                if (isset($objectArray['depends'])) {
                    foreach ($objectArray['depends'] as $dependData) {
                        // Connect to expected container
                        if (is_array($dependData)) {
                            if (!isset($dependData['class'])) {
                                throw new ConfigurationException(
                                    'The "class" index of the container "' . $containerName . '" is not detected!'
                                );
                            }

                            if (!class_exists($class = $dependData['class'])) {
                                throw new RuntimeException('Class "' . $class . '" doesn\'t exists!');
                            }

                            $expectedClass = $class;

                            if (!isset($dependData['owner'])) {
                                throw new ConfigurationException(
                                    'The "owner" index of the container "' . $containerName . '" is not detected!'
                                );
                            }

                            $ownerOfExpectedClass = $dependData['owner'];
                            $source = container($ownerOfExpectedClass);

                            if (!$source) {
                                throw new NotFoundException('Container ' . $ownerOfExpectedClass . ' not registered!');
                            }

                            if (!$source->has($expectedClass)) {
                                throw new NotFoundException('Container ' . $expectedClass . ' not registered!');
                            }

                            $params[$expectedClass] = $source->get($expectedClass);
                        } else {
                            if (!$container->has($dependData)) {
                                throw new NotFoundException('Object ' . $dependData . ' not registered at container ' . $containerName);
                            }
                        }
                    }
                }

                $container->register($objectArray['class'], $params);
            }
        }
    }

}