<?php

declare(strict_types=1);

namespace joole\framework\routing;

use joole\framework\Application;
use joole\framework\component\BaseComponent;

/**
 * Class BaseRouter
 *
 * Base router for web application.
 *
 * @package joole\framework\routing
 */
class BaseRouter extends BaseComponent implements Router
{

    private array $routes = [];

    public function load(array $data = []): void
    {
        // TODO: Implement load() method.
    }

    public static function to(array $data): string
    {
        // TODO: Implement to() method.
    }

    public function init(array $options): void
    {

    }

    public function run(Application $app): void
    {
        $app->setRouter($this);
    }

    public function handleRequest(): void
    {
    }
}