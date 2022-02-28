<?php

declare(strict_types=1);

namespace joole\framework\data\types;

use ArrayAccess;
use function array_keys;

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
     * @throws ImmutableException
     * @deprecated Not needs at this class.
     */
    public function offsetSet($offset, $value)
    {
        throw new ImmutableException('Can\'t set value for array: array is an immutable.');
    }

    /**
     * @inheritDoc
     * @throws ImmutableException
     * @deprecated Not needs at this class
     */
    public function offsetUnset($offset)
    {
        throw new ImmutableException('Can\'t unset value for array: array is an immutable.');
    }

    /**
     * Returns keys of items.
     *
     * @return array
     */
    public function keys(): array
    {
        return array_keys($this->items);
    }
}