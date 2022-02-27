<?php

declare(strict_types=1);

namespace joole\framework\data\container;

use AssertionError;
use ErrorException;
use ReflectionException;
use ReflectionClass;

use function count;
use function call_user_func_array;
use function is_null;

/**
 * A container formed from an abstraction.
 */
class Container extends BaseContainer
{
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
        if($reflectedObject->hasMethod('__construct')){
            $constructor = $reflectedObject->getMethod('__construct');
            $params = $constructor->getParameters();

            // If method has 0 parameters -> empty construct calling
            if(count($params) === 0){
                $builtObject = new $object();
            }else{
                $arguments = [];

                foreach ($params as $param){
                    /** @var \ReflectionNamedType $class */
                    $type = $param->getType();
                    $class = (string)$type->getName();
                    $paramPos = $param->getPosition();

                    // If argument is not class, we will declare argument from params by param name.
                    if(!class_exists($class)){
                        $name = $param->getName();

                        if(!isset($params[$name])){
                            if($param->isOptional()){
                                $arguments[$paramPos] = $param->getDefaultValue();

                                continue;
                            }

                            throw new ErrorException('Param with name '.$name.' doesn\'t exist in $params array!');
                        }

                        $arguments[$paramPos] = $params[$name];
                    }else{

                        if(!$this->has($class)){
                            throw new ErrorException('Container of class '.$class.' doesn\'t exists!');
                        }

                        $arguments[$paramPos] = $this->get($class);
                    }
                }

                $builtObject = $reflectedObject->newInstanceArgs($arguments);
            }
        }else{
            $builtObject = new $object();
        }

        self::$instances[$object] = $builtObject;
    }

    /**
     * @param array $params
     * @param string ...$objects
     * @throws ErrorException
     * @throws ReflectionException|\joole\framework\data\container\NotFoundException
     */
    public function multiplePush(array $params, string ...$objects): void
    {
        foreach ($objects as $object){
            $this->register($object, $params);
        }
    }
}