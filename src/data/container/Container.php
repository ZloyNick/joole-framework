<?php

declare(strict_types=1);

namespace joole\framework\data\container;

use AssertionError;
use ErrorException;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use function class_exists;
use function count;

/**
 * A container formed from an abstraction.
 */
class Container extends BaseContainer
{
    /**
     * @param array $params
     * @param string ...$objects
     * @throws ErrorException
     * @throws ReflectionException|\joole\framework\data\container\NotFoundException
     */
    public function multiplePush(array $params, string ...$objects): void
    {
        foreach ($objects as $object) {
            $this->register($object, $params);
        }
    }

    /**
     * @param string $object
     * @param string[] $params
     * @throws ReflectionException
     * @throws ErrorException|\joole\framework\data\container\NotFoundException
     */
    public function register(string $object, array $params = []): void
    {
        // Twin given
        if ($this->has($object)) {
            throw new AssertionError($object . ' can\'t be asserted to this container! This object already exists.');
        }

        $reflectedObject = new ReflectionClass($object);

        // If object hasn't constructor
        if ($reflectedObject->hasMethod('__construct')) {
            $builtObject =
                count($reflectedObject->getMethod('__construct')->getParameters()) === 0
                    ? new $object() : $this->initComponentWithConstructor($reflectedObject, $params);
        } else {
            $builtObject = new $object();
        }

        $this->instances[$object] = $builtObject;
    }

    /**
     * Returns constructed object.
     *
     * Creates object with constructor with classes from current container
     * and given parameters.
     *
     * @param ReflectionClass $reflectedObject Reflected class.
     * @param array $entryParams = [
     *      'age' => 100,// If __construct method has param $age
     *      'name' => 'ExampleName',// If __construct method has param $name
     * ]
     *
     * @return object Constructed object.
     *
     * @throws ErrorException
     * @throws NotFoundException
     * @throws ReflectionException
     */
    private function initComponentWithConstructor(ReflectionClass $reflectedObject, array $entryParams = []): object
    {
        $constructor = $reflectedObject->getMethod('__construct');
        $params = $constructor->getParameters();

        $arguments = [];

        foreach ($params as $param) {
            /** @var ReflectionNamedType $class */
            $type = $param->getType();
            $class = (string)$type->getName();
            $paramPos = $param->getPosition();

            // If argument is not class or class $class doesn't exists,
            // we will declare argument from params by param name.
            if (!class_exists($class)) {
                $name = $param->getName();

                // If entry param with name $name doesn't exists
                if (!isset($entryParams[$name])) {
                    // If param $name have default value, it will be set to default value
                    if ($param->isOptional()) {
                        $arguments[$paramPos] = $param->getDefaultValue();

                        continue;
                    }

                    throw new ErrorException('Param with name ' . $name . ' doesn\'t exist in $params array!');
                }

                $arguments[$paramPos] = $entryParams[$name];

                continue;
            }else{
                // Container dependency checking
                if (isset($entryParams[$class])) {
                    $arguments[$paramPos] = $entryParams[$class];

                    continue;
                }
            }

            // Local search
            $arguments[$paramPos] = $this->get($class);
        }

        return $reflectedObject->newInstanceArgs($arguments);
    }
}