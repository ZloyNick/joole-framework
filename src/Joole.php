<?php

declare(strict_types=1);

namespace joole\framework;

use joole\framework\component\ComponentInterface;
use joole\framework\data\container\Container;
use joole\framework\data\container\NotFoundException;
use joole\framework\data\types\ImmutableArray;
use LogicException;

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
     * Property unset action.
     *
     * The call is processed before the property is reset.
     *
     * @param string $name
     */
    public function __unset(string $name): void
    {
        // if property doesn't exists
        if(!isset($this->{$name})){
            return;
        }

        // We can't unset an immutable objects
        if($this->{$name} instanceof ImmutableArray){
            throw new LogicException('Immutable objects can\'t be reset.');
        }

        unset($this->{$name});
    }

    /**
     * Initialize Joole Framework
     */
    public function init(array $data):void{
        if(isset($data['containers'])){
            self::$containers = new ImmutableArray();
            $this->registerContainers($data['containers']);
        }

        if(isset($data['components'])){
            $this->registerContainers($data['containers']);
        }
    }

    /**
     *
     */
    private function registerContainers(array $containers):void{
        if(!class_exists('joole\reflector\Reflector')){
            //TODO: debug
            return;
        }

        $reflector = new \joole\reflector\Reflector();
        $registeredContainers = self::$containers;
        $reflectedContainers = $reflector->buildFromObject($registeredContainers);
        $containerNames = array_keys($containers);

        foreach ($containerNames as $name){
            $containerNames[$name] = new Container();
        }

        $reflectedContainers->getProperty('items')->setValue($containerNames);

        foreach ($containers as $containerName => $containerData){
            /** @var Container $container */
            $container = $registeredContainers[$containerName];

            foreach ($containerData as $objectArray) {
                if (isset($objectArray['depends'])) {
                    foreach ($objectArray['depends'] as $dependedContainer) {
                        if (!$container->has($dependedContainer)) {
                            throw new NotFoundException('Container ' . $dependedContainer . ' not registered!');
                        }
                    }
                }

                $container->register($objectArray['class']);
            }
        }
    }

    /**
     * Returns container or null.
     *
     * @param string|null $id
     * @return \joole\framework\data\container\Container|null Returns first container or null
     */
    public static function getContainer(null|string $id = null) : null|Container{
        $containers = self::$containers;

        if($id){
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
    public static function getComponent(string $id) : null|ComponentInterface{
        return self::$components[$id] ?? null;
    }

}