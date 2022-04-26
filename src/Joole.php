<?php

declare(strict_types=1);

namespace joole\framework;

use joole\framework\component\BaseComponent;
use joole\framework\component\ComponentInterface;
use joole\framework\data\types\ImmutableArray;
use joole\framework\exception\config\ConfigurationException;
use RuntimeException;
use function class_exists;
use function is_subclass_of;


/**
 * Main Framework's class
 */
final class Joole
{

    /**
     * Components.
     *
     * @var ImmutableArray
     */
    public static ImmutableArray $components;

    /**
     * An application's instance.
     *
     * DO NOT USE Joole::$app = ...
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

}