<?php

declare(strict_types=1);

namespace joole\framework\component;

use function bin2hex;
use function random_bytes;

/**
 * Component class.
 */
abstract class BaseComponent implements ComponentInterface
{

    private int|string $id;

    /**
     * BaseComponent constructor.
     * @param int|string|null $id If null given - generates random string(6)
     * @throws \Exception
     */
    public function __construct(null|int|string $id = null)
    {
        $this->id = $id ?? bin2hex(random_bytes(3));
    }

    /**
     * Returns component's id.
     *
     * @return string|int
     */
    final public function getId(): string|int
    {
        return $this->id;
    }

}