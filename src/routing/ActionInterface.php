<?php

declare(strict_types=1);

namespace joole\framework\routing;

use joole\framework\http\response\BaseResponse;
use joole\framework\validator\http\RequestValidatorInterface;

/**
 * An action interface.
 */
interface ActionInterface
{

    /**
     * Requires validators for action.
     *
     * Notice: existing validators will merge with given.
     *
     * Example:
     *
     * <code>
     * ```
     * Router::register('test.route', "\Controller@testAction")
     *      ->withValidators([\CreatePersonValidator::class]);
     * ```
     * </code>
     *
     * @param string[]|RequestValidatorInterface[] $validators
     *
     * If given value is string, it must be instanceof RequestValidatorInterface
     *
     * @return static Current action class.
     */
    public function withValidators(string|RequestValidatorInterface ...$validators):static;

    /**
     * Returns all validators for action.
     *
     * @return RequestValidatorInterface[]
     */
    public function getValidators():array;

    /*
     * Requires middlewares for action.
     *
     * Example:
     *
     * <code>
     * ```
     * Router::register('posts.create', "\PostsController@createPost")
     *      ->withMiddlewares([\AuthValidator::class, \PermissionsValidator::class]);
     * ```
     * </code>
     *
     * @return static
     */
    //public function withMiddlewares():static;

    /**
     * Executes action.
     *
     * @param array $params Action params.
     *
     * @return BaseResponse
     */
    public function execute(array $params):BaseResponse;

}