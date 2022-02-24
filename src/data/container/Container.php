<?php

declare(strict_types=1);

namespace joole\framework\data\container;

use AssertionError;
use ErrorException;
use InvalidArgumentException;
use joole\framework\data\container\object\ContainerObject;
use ReflectionException;
use ReflectionClass;

use function is_subclass_of;
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
    public function register(string $object, array $params): void
    {
        // Only subclass of ContainerObject can be asserted
        if (!is_subclass_of($object, $class = ContainerObject::class)) {
            throw new InvalidArgumentException('Argument 1 must be instance of ' . $class, '!');
        }

        // Twin given
        if ($this->has($object)) {
            throw new AssertionError($object . ' can\'t be asserted to this container! This object already exists.');
        }

        $reflectedObject = new ReflectionClass($object);

        // If object hasn't constructor
        try{
            $constructor = $reflectedObject->getMethod('__construct');
            $params = $constructor->getParameters();

            // If method has 0 parameters -> empty construct calling
            if(count($params) === 0){
                $builtObject = new $object();
            }else{
                $arguments = [];

                foreach ($params as $param){
                    $class = $param->getDeclaringClass();

                    // If argument is not class, we will declare argument from params by param name.
                    if(is_null($class)){
                        $name = $param->getName();

                        if(!isset($params[$name])){
                            throw new ErrorException('Param with name '.$name.' doesn\'t exist in $params array!');
                        }

                        $arguments[] = $params[$name];
                    }else{
                        $className = $class->getName();

                        if(!$this->has($className)){
                            throw new ErrorException('Container of class '.$className.' doesn\'t exists!');
                        }

                        $arguments[] = $this->get($className);
                    }
                }

                $builtObject = call_user_func_array([$object, '__construct'], $arguments);
            }
        }catch(ReflectionException $exception){
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