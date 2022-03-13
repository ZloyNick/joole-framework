<?php

declare(strict_types=1);

namespace joole\framework\routing;

use Closure;
use joole\framework\Application;
use joole\framework\component\BaseComponent;
use joole\framework\controller\ControllerInterface;
use joole\framework\exception\component\ComponentException;
use joole\framework\exception\config\ComponentConfigurationException;
use joole\framework\http\BaseRequest;
use ReflectionClass;
use ReflectionFunction;

use ReflectionFunctionAbstract;
use function request;

/**
 * Class BaseRouter
 *
 * Base router for web application.
 *
 * You can see docs here:
 *
 * @see \joole\framework\routing\BaseRouter::register()
 * @see \joole\framework\routing\BaseRouter::to()
 * @see \joole\framework\routing\BaseRouter::toRoute()
 *
 * @package joole\framework\routing
 */
class BaseRouter extends BaseComponent implements Router
{

    /** @var array */
    private static array $actions = [];
    private static array $routes = [];

    public function load(array $data = []): void
    {
        // TODO: Cache.
    }

    /**
     * Registers action and route.
     *
     * @param string $route Route name. Use it for find action.
     * If action has binds, set it:
     * Example:
     * <code>
     * ```
     *      // Route "your.route" has action: /your/route/:id
     *      BaseRouter::toRoute(['your.route', ['id' => $id]]);
     * ```
     * </code>
     *
     * @param string $action An action. You can bind it dynamic: /my/action/:name/:and_id.
     * How to use it binds? Declare variables with the same name in the method/function:
     * <code>
     *  ```
     *      BaseRouter::register('my', "/my/action/:name/:and_id", function(string $name, int $and_id){
     *          // do something
     *      });
     *  ```
     * </code>
     *
     * @param array|string|callable|Closure $callback Action runtime path.
     * You can use function as runtime action. Or controller and method.
     * Example for controller:
     * <code>
     *  ```
     *      BaseRouter::register('my', "/my", [MyController::class, "controllerMethodMy"]);
     *      BaseRouter::register('my.action', "/my/action", [MyController::class, "controllerMethodMyAction"]);
     *      // You also can use string
     *      BaseRouter::register('my.action', "/my/action", "\MyController@controllerMethodMyAction");
     *  ```
     * </code>
     * @return static
     * @throws ComponentException
     * @todo: request validator
     *
     * @todo: middlewhare
     */
    public static function register(string $route, string $action, array|string|callable|Closure $callback): static
    {
        // Routes
        $routes = &self::$routes;
        // Actions
        $actions = &self::$actions;

        // If action starts with '/', we must remove it.
        if (substr($action, 0, 1) === '/') {
            $action = substr($action, 1);
        }

        // Existing actions and routes checking.
        isset($routes[$route])
            ? throw new ComponentException('Route with name "' . $route . '" already exist. Please, rename your route.')
            : (isset($actions[$action])
            ? throw new ComponentException('Action with name "' . $action . '" already exist. Please, rename your action.') : 1);

        // Actions as array.
        $actionParts = explode('/', $action);
        // First action (main).
        $mainAction = $actionParts[0];
        // Last action key.
        $lastSubActionKey = count($actionParts) - 1;

        // Removing main action.
        unset($actionParts[0]);

        // If last action not set and have empty value (last '/' defect).
        if (isset($actionParts[$lastSubActionKey]) && empty($actionParts[$lastSubActionKey])) {
            // Last action key if defect of last '/' given.
            $lastSubActionKey = count($actionParts) - 2;

            // Removing defected part.
            unset($actionParts[count($actionParts) - 1]);
        }

        // If action not set.
        if (!isset($actions[$mainAction])) {
            // Setting actions array for main action.
            $actions[$mainAction] = [];
            // Reference for recursive writing.
            $subActions = &$actions[$mainAction];

            // If no have sub actions.
            if (count($subActions) === 0) {
                // Id for callback is unique and mustn't repeat.
                $subActions = ['runtime.' . $mainAction => $callback];
            }

            // Registering sub actions.
            foreach ($actionParts as $key => $actionPart) {
                // If action is last => registering.
                if ($lastSubActionKey === $key) {
                    // Id for callback is unique and mustn't repeat.
                    $subActions[$actionPart] = ['runtime.' . $actionPart => $callback];

                    break;
                }

                // Registering new actions array.
                $subActions[$actionPart] = [];
                // Getting next sub action values.
                $subActions = &$subActions[$actionPart];
            }
        } else {
            // Reference for registering sub action recursive.
            $subActions = &$actions[$mainAction];

            // Registering sub actions.
            foreach ($actionParts as $key => $actionPart) {
                // If action is last => registering.
                if ($lastSubActionKey === $key) {
                    // Id for callback is unique and mustn't repeat.
                    $subActions[$actionPart] = ['runtime.' . $actionPart => $callback];

                    break;
                }

                // If sub action not set => registering an empty array.
                if (!isset($subActions)) {
                    $subActions[$actionPart] = [];
                }

                // Next sub action values.
                $subActions = &$subActions[$actionPart];
            }
        }

        // Route setting
        $routes[$route] = $action;

        return new static();
    }

    final public static function toRoute(array $data): string
    {
        // Route is required param.
        if(count($data) < 1 || !isset($data[0])){
            throw new ComponentException('Route can\'t be null!');
        }

        // Route must be a string.
        if(!is_string($route = $data[0])){
            throw new ComponentException('Given route param must be instance of string! '.gettype($route).' given.');
        }

        $routes = &self::$routes;

        // If route not registered.
        if(!isset($routes[$route])){
            throw new ComponentException('Route with name '.$route.' not found!');
        }

        $action = $routes[$route];

        // Binding params
        if(isset($data[1]) && is_array($data[1])){
            foreach ($data[1] as $param => $value){
                if(is_string($param)){
                    $action = str_replace(':'.$param, (string)$value, $action);
                }
            }
        }

        return $action;
    }

    final public static function to(array $data): string
    {

    }

    /**
     * @param array $options
     * @throws \joole\framework\exception\config\ComponentConfigurationException
     */
    final public function init(array $options): void
    {
        if (!isset($options['routes'])) {
            throw new ComponentConfigurationException('Parameter "routes" not found at component ' . $this->getId());
        }

        $routesPath = $options['routes'];

        if (!is_dir($routesPath)) {
            !file_exists($routesPath) ?
                throw new ComponentConfigurationException('Routes path "' . $routesPath . '" not found.')
                : (substr($routesPath, -4, 4) !== '.php' ?
                throw new ComponentConfigurationException('Routes file "' . $routesPath . '" haven\'t ".php" extension.')
                : require_once $routesPath
            );
        } else {
            foreach (scan_dir($routesPath) as $routesFile) {
                if (substr($routesFile, -4, 4) !== '.php') {
                    continue;
                }

                require_once $routesPath . DIRECTORY_SEPARATOR . $routesFile;
            }
        }
    }

    final public function run(Application $app): void
    {
        // Setting router class for application.
        $app->setRouter($this);
    }

    /**
     * Handles raw request.
     *
     * @throws \ReflectionException
     * @throws \joole\framework\exception\component\ComponentException
     */
    final public function handleRequest(): void
    {
        // URI as object.
        $requestUri = request()->getUri();
        // Action.
        $action = $requestUri->getPath();
        // Runtime path and bound action params.
        [$callback, $params] = self::getActionCallback($action);

        // Closure call
        if ($callback instanceof Closure) {
            $this->closure($callback, $params);
        } else {
            // If runtime using "controller@method" pattern.
            if (is_string($callback)) {
                // [Controller::class, "action"]
                $callback = explode('@', $callback);
            }

            // Checking for the existence of the controller element
            if (!isset($callback[0])) {
                throw new ComponentException('Controller not included. Please, use "[\ControllerClass::class, "method"]" array!');
            }

            //Checking for the existence of the method element
            if (!isset($callback[1])) {
                throw new ComponentException('Method not included. Please, use "[\ControllerClass::class, "method"]" array!');
            }

            $this->controller($callback[0], $callback[1], $params);
        }
    }

    /**
     * Returns the parameters for calling the function in order,
     * while using the parameters from the action and,
     * if they are not found, sets the default values.
     *
     * @param ReflectionFunctionAbstract $function Reflected method of anonymous function.
     * @param array $actionParams Params from action.
     * @return array Function params.
     *
     * @throws ComponentException
     * @throws \ReflectionException
     */
    private static function getConstructorParams(ReflectionFunctionAbstract $function, array $actionParams = []){
        // Params of function.
        $params = $function->getParameters();
        // Array of parameters in order.
        $functionCallParams = [];

        foreach ($params as $param) {
            $name = $param->getName();// Param name.
            $type = $param->getType();// Type class (reflected).
            $class = (string)$type->getName();// Class or name of type.
            $paramPos = $param->getPosition();// Param position

            // Allows Request class only.
            if (is_subclass_of($class, BaseRequest::class)) {
                // Writing request.
                $functionCallParams[$paramPos] = request();

                continue;
            }

            // If it's not bound param
            if (!isset($actionParams[$name])) {
                // If param has default value
                if ($param->isOptional()) {
                    // Binding default value
                    $functionCallParams[$paramPos] = $param->getDefaultValue();
                }

                throw new ComponentException('Param $' . $name . ' not set for function');
            } else {
                // If it's bound param => setting it.
                $functionCallParams[$paramPos] = $actionParams[$name];
            }
        }

        return $functionCallParams;
    }

    /**
     * This method finds runtime path for given action.
     *
     * @param string $action Action.
     * @return array Array of runtime path and bound params.
     *
     * @throws ComponentException
     */
    private static function getActionCallback(string $action): array
    {
        // Removing first path symbol.
        if (substr($action, 0, 1) === '/') {
            $action = substr($action, 1);
        }

        // Removing last path symbol.
        if (substr($action, -1, 1) === '/') {
            $action = substr($action, 0, -1);
        }

        // Copy of actions for recursive search.
        $actions = self::$actions;
        // Actions as array
        // Example: $action = 'my/action'
        // $parts = ['my', 'action']
        // It's using for search runtime path.
        $actionParts = explode('/', $action);
        // Last key using for get runtime path.
        $lastKey = count($actionParts) - 1;
        // Bind params from actions.
        $params = [];

        foreach ($actionParts as $key => $part) {
            // If param not found in registered actions, it
            // may be bound param.
            if (!isset($actions[$part])) {
                // Current actions keys
                $keys = array_keys($actions);

                // Searching bind key...
                foreach ($keys as $actionName) {
                    // If one of remaining actions has a symbol ":" at the beginning => its bind
                    if (substr($actionName, 0, 1) === ':') {
                        // Bound param without ":"
                        $boundParamName = substr($actionName, 1);
                        // Addition param to array
                        $params[$boundParamName] = $part;
                        // This is done for the last check and getting the runtime path by index
                        $part = $actionName;
                    }
                }
            }

            // next child action
            $actions = $actions[$part];

            // If current action given.
            if ($lastKey === $key) {
                // If runtime path not existing.
                if (!isset($actions['runtime.' . $part])) {
                    throw new ComponentException('Runtime of action "' . $action . '" not found!');
                }

                // Returning runtime path and params from bind
                return [$actions['runtime.' . $part], $params];
            }
        }

        throw new ComponentException('Runtime of action "' . $action . '" not found!');
    }

    /**
     * This method calls Closure with params from action.
     *
     * If action not provides value for method => using default.
     *
     * @param Closure $closure Closure for action.
     * @param array $actionParams Params from action.
     *
     * @throws ComponentException
     * @throws \ReflectionException
     */
    public function closure(Closure $closure, array $actionParams = []):mixed
    {
        // Reflected function using in function for constructor param parsing.
        $reflectedClosure = new ReflectionFunction($closure);
        // Built params from method params.
        $params = self::getConstructorParams($reflectedClosure, $actionParams);

        return call_user_func_array($closure, $params);
    }

    /**
     * This method calls controller method with params from action.
     *
     * If action not provides value for method => using default.
     *
     * @param string $controller Controller as string class.
     * @param string $method Method as string
     * @param array $actionParams Params from action.
     *
     * @throws ComponentException
     * @throws \ReflectionException
     */
    public function controller(string $controller, string $method, array $actionParams = []):mixed{
        // If class not exist.
        if(!class_exists($controller)){
            throw new ComponentException('Controller class '.$controller.' doesn\'t exist');
        }

        // Controller must be instance of ControllerInterface!
        if(!is_subclass_of($controller, ControllerInterface::class)){
            throw new ComponentException('Controller '.$controller.' must be instance of '.ControllerInterface::class);
        }

        /**
         * TODO: building controller with using containers and them objects.
         *
         * @var ControllerInterface $controller
         */
        $controller = new $controller();
        // Reflected method using in function for constructor param parsing.
        $reflectedMethod = (new ReflectionClass($controller))->getMethod($method);
        // Built params from method params.
        $params = self::getConstructorParams($reflectedMethod, $actionParams);

        // Calling.
        return call_user_func_array([$controller, $method], $params);
    }
}