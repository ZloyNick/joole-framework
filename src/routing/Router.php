<?php

declare(strict_types=1);

namespace joole\framework\routing;

use Closure;
use joole\framework\exception\component\ComponentException;
use joole\framework\http\request\BaseUri;
use joole\framework\http\response\BaseResponse;

/**
 * A basic router interface.
 */
interface Router
{

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
     *
     * @return ActionInterface|null
     *
     * @throws ComponentException
     *
     * @todo: middlewares
     */
    public static function register(string $route, string $action, array|string|callable|Closure $callback): ?ActionInterface;

    /**
     * Loads routes and rules.
     *
     * @param array $data
     */
    public function load(array $data = []): void;

    /**
     * Builds url with params.
     *
     * @param array $data =
     * <code>
     * [
     *      'examplePath',
     *      ['example_param' => 'value']
     * ]
     * </code>
     *
     * @return BaseUri a url model.
     */
    public static function to(array $data): BaseUri;

    /**
     * Registers action and route.
     *
     * @param array $data ['route.name', bindParams[]] Route and bind params.
     *
     * If action has binds, set it:
     * Example:
     * <code>
     * ```
     *      // Route "your.route" has action: /your/route/:id
     *      BaseRouter::toRoute(['your.route', ['id' => $id]]);
     * ```
     * </code>
     *
     * @return BaseUri Action.
     */
    public static function toRoute(array $data): BaseUri;

    /**
     * Handles request.
     *
     * @return BaseResponse
     */
    public function handleRequest(): BaseResponse;

}