<?php

declare(strict_types=1);

namespace joole\framework\routing;

use Closure;
use joole\framework\Application;
use joole\framework\component\BaseComponent;
use joole\framework\exception\component\ComponentException;
use joole\framework\exception\config\ComponentConfigurationException;
use joole\framework\http\request\BaseUri;

use function request;
use function str_starts_with;
use function str_ends_with;

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
 */
class BaseRouter extends BaseComponent implements Router
{

    /** @var array Actions list */
    private static array $actions = [];
    /** @var array Routes list */
    private static array $routes = [];

    public function load(array $data = []): void
    {
        // TODO: Cache.
    }

    public static function register(string $route, string $action, array|string|callable|Closure $callback): ?ActionInterface
    {
        // Routes
        $routes = &self::$routes;
        // Actions
        $actions = &self::$actions;

        // If action starts with '/', we must remove it.
        if (str_starts_with($action, '/')) {
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
                $subActions = [];
            }

            // Registering sub actions.
            foreach ($actionParts as $key => $actionPart) {
                // If action is last => registering.
                if ($lastSubActionKey === $key) {
                    // Id for callback is unique and mustn't repeat.
                    $subActions[$actionPart] = ['runtime.' . $actionPart => new BaseAction($actionPart, $callback)];

                    return $subActions[$actionPart]['runtime.' . $actionPart];
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
                    $subActions[$actionPart] = ['runtime.' . $actionPart => new BaseAction($actionPart, $callback)];

                    return $subActions[$actionPart]['runtime.' . $actionPart];
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

        return null;
    }

    final public static function toRoute(array $data): BaseUri
    {
        static::validateToArgs($data);

        $routes = &self::$routes;
        $route = $data[0];

        // If route not registered.
        if(!isset($routes[$route])){
            throw new ComponentException('Route with name '.$route.' not found!');
        }

        $action = $routes[$route];

        if(!isset($data[1])){
            $data[1] = [];
        }

        return static::to([$action, $data[1]]);
    }

    protected static function validateToArgs(array $data):void{
        // Route is required param.
        if(count($data) < 1 || !isset($data[0])){
            throw new ComponentException('Route can\'t be null!');
        }

        // Route must be a string.
        if(!is_string($route = $data[0])){
            throw new ComponentException('Given action or route param must be instance of string! '.gettype($route).' given.');
        }
    }

    final public static function to(array $data): BaseUri
    {
        // Validation
        static::validateToArgs($data);

        // action
        $action = $data[0];
        // query params
        $queryParams = [];

        if(isset($data[1]) && is_array($data[1])){
            $queryParams = $data[1];

            foreach ($data[1] as $param => $value) {
                if(is_string($param) && stripos($action, ':'.$param) !== false){
                    $action = str_replace(':'.$param, (string)$value, $action);

                    unset($queryParams[$param]);
                }
            }
        }

        return request()->getUri()->withPath($action)->withQuery(
            BaseUri::generateQueryString($queryParams)
        );
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
                : (!str_ends_with($routesPath, '.php') ?
                throw new ComponentConfigurationException('Routes file "' . $routesPath . '" haven\'t ".php" extension.')
                : require_once $routesPath
            );
        } else {
            foreach (scan_dir($routesPath) as $routesFile) {
                if (!str_ends_with($routesFile, '.php')) {
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
        // Action path.
        $action = $requestUri->getPath();
        // Action class and bound action params.
        [$action, $params] = self::getAction($action);

        /**
         * @var ActionInterface $action
         */
        $action->execute($params);
    }

    /**
     * This method finds runtime path for given action.
     *
     * @param string $action Action.
     * @return array Array of runtime path and bound params.
     *
     * @throws ComponentException
     */
    private static function getAction(string $action): array
    {
        // Removing first path symbol.
        if (str_starts_with($action, '/')) {
            $action = substr($action, 1);
        }

        // Removing last path symbol.
        if (str_ends_with($action, '/')) {
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
                    if (str_starts_with($actionName, ':')) {
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
                if (!isset($actions['runtime.'.$part])) {
                    throw new ComponentException('Class of action "' . $action . '" not found!');
                }

                // Returning runtime path and params from bind
                return [$actions['runtime.' . $part], $params];
            }
        }

        throw new ComponentException('Class of action "' . $action . '" not found!');
    }

}