<?php

declare(strict_types=1);

namespace joole\framework\data\types;

use ArrayAccess;

/**
 * Class ImmutableArray array allows read-only action.
 */
final class ImmutableArray implements ArrayAccess
{

    /**
     * Items, that can be read.
     *
     * @var array
     */
    private array $items = [];

    /**
     * @inheritDoc
     */
    public function offsetExists($offset): bool
    {
        return isset($this->items[$offset]);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($offset)
    {
        return $this->items[$offset] ?? null;
    }

    /**
     * @inheritDoc
     * @throws TypeException
     * @deprecated Not needs at this class.
     */
    public function offsetSet($offset, $value)
    {
        throw new TypeException('Can\'t set value for array: array is an immutable.');
    }

    /**
     * @inheritDoc
     * @throws TypeException
     * @deprecated Not needs at this class
     */
    public function offsetUnset($offset)
    {
        throw new TypeException('Can\'t unset value for array: array is an immutable.');
    }

    /**
     * Destruct of this object not allowed.
     *
     * The call is processed before the object is destroyed.
     *
     * @throws TypeException
     */
    public function __destruct()
    {
        throw new TypeException('Unset of immutable array is not allowed.');
    }
}