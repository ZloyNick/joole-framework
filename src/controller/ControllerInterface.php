<?php

declare(strict_types=1);

namespace joole\framework\controller;

/**
 * Interface ControllerInterface
 *
 * A base interface for controllers.
 */
interface ControllerInterface
{

    /**
     * Calls before action release.
     *
     * @param string $method Method for release.
     */
    public function beforeAction(string $method): void;

    /**
     * Calls after action release.
     *
     * @param string $method Method for release.
     */
    public function afterAction(string $method): void;

}