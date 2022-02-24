<?php

declare(strict_types=1);

namespace joole\framework;

use joole\framework\data\types\ImmutableArray;
use LogicException;

/**
 * Main Framework's class
 */
final class Joole
{

    /**
     * Containers.
     *
     * @var ImmutableArray
     */
    public static ImmutableArray $containers;

    /**
     * Components.
     *
     * @var ImmutableArray
     */
    public static ImmutableArray $components;

    /**
     * Property unset action.
     *
     * The call is processed before the property is reset.
     *
     * @param string $name
     */
    public function __unset(string $name): void
    {
        // if property doesn't exists
        if(!isset($this->{$name})){
            return;
        }

        // We can't unset an immutable objects
        if($this->{$name} instanceof ImmutableArray){
            throw new LogicException('Immutable objects can\'t be reset.');
        }

        unset($this->{$name});
    }

}