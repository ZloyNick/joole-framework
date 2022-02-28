<?php

declare(strict_types=1);

namespace joole\framework\component;

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
     */
    public function init(): void;

    /**
     * Executes component.
     */
    public function run(): void;

}