<?php

declare(strict_types=1);

namespace joole\framework\routing;

/**
 * A basic router interface.
 *
 * @package joole\framework\routing
 */
interface Router
{

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
     *      'example_param' => 'value',
     * ]
     * </code>
     * @return string Path url.
     */
    public static function to(array $data): string;

    /**
     * Handles request.
     *
     * @return mixed
     */
    public function handleRequest(): void;

}