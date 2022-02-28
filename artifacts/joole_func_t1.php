<?php

declare(strict_types=1);

use joole\framework\data\container\Container;
use joole\framework\Joole;

/**
 * Returns container by name.
 *
 * @param string $name Container's name
 * @return Container|null
 */
function container(string $name) : ?Container{
    return Joole::getContainer($name);
}
