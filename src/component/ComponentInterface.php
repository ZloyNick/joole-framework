<?php

declare(strict_types=1);

namespace joole\framework\component;

use joole\framework\Application;

/**
 * An interface of component
 */
interface ComponentInterface
{

    /**
     * ComponentInterface constructor.
     *
     * Component hasn't constructor.
     */
    public function __construct(int|string $id);

    /**
     * Initializes component.
     *
     * @param array $options Component's options.
     */
    public function init(array $options): void;

    /**
     * Interaction with the web application.
     *
     * @param Application $app
     */
    public function run(Application $app): void;

}