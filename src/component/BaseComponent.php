<?php

declare(strict_types=1);

namespace joole\framework\component;

use joole\framework\Application;
use function bin2hex;
use function random_bytes;

/**
 * Component class.
 */
abstract class BaseComponent implements ComponentInterface
{

    /**
     * Component's id.
     *
     * @var int|string
     */
    private int|string $id;
    /**
     * Options for component.
     *
     * @var array
     */
    private array $options = [];

    /**
     * BaseComponent constructor.
     *
     * @param int|string|null $id If null given - generates random string(6)
     * @throws \Exception
     */
    final public function __construct(null|int|string $id = null)
    {
        $this->id = $id ?? bin2hex(random_bytes(3));
    }

    abstract public function init(array $options): void;

    /**
     * Returns component's id.
     *
     * @return string|int
     */
    final public function getId(): string|int
    {
        return $this->id;
    }

    /**
     * Runs component with options.
     */
    abstract public function run(Application $app): void;

    /**
     * Returns component's options.
     *
     * @return array
     */
    final public function getOptions(): array
    {
        return $this->options;
    }

}