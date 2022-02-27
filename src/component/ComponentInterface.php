<?php

declare(strict_types=1);

namespace joole\framework\component;

use Closure;

/**
 * An interface of component
 */
interface ComponentInterface
{

    /**
     * Col
     */
    public function run():void;

}