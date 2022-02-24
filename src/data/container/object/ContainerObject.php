<?php

declare(strict_types=1);

namespace joole\framework\data\container\object;

/**
 * An interface of container's object.
 */
interface ContainerObject
{

    /**
     * Returns name of container.
     *
     * @return string
     */
    public function getName(): string;

}