<?php

declare(strict_types=1);

namespace joole\framework\component;

use Closure;
use joole\framework\data\types\ImmutableArray;

/**
 * Component class.
 */
abstract class BaseComponent implements ComponentInterface
{

    /** @var string Unique id of component */
    private string $id;

    /** @var Closure|array|null Before load data/method */
    protected null|Closure|array $beforeLoad;
    /** @var Closure|array|null Load data/method */
    protected null|Closure|array $load;
    /** @var Closure|array|null Before unload data/method */
    protected null|Closure|array $beforeUnload;
    /** @var Closure|array|null Unload data/method */
    protected null|Closure|array $unload;

    /** @var \joole\framework\data\types\ImmutableArray Component's configuration */
    protected ImmutableArray $config;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    /**
     * Returns an unique component's id
     *
     * @return string
     */
    final public function getId(): string
    {
        return $this->id;
    }

    public function beforeLoad(array|Closure|null $callback): void
    {
        $callback ?? ($this->beforeLoad = $callback);
    }

    public function onLoad(array|Closure|null $callback): void
    {
        $callback ?? ($this->load = $callback);
    }

    public function beforeUnload(array|Closure|null $callback): void
    {
        $callback ?? ($this->beforeUnload = $callback);
    }

    public function unload(array|Closure|null $callback): void
    {
        $callback ?? ($this->unload = $callback);
    }

    /**
     * Initializes component
     *
     * @param array $config = [
     *      "depends": [\My\Example\Component::class],// Checks loaded com
     * ]
     */
    public function init(array $config = [])
    {
        if(isset($config['beforeLoad'])){
            $this->beforeLoad($config['beforeLoad']);
        }

        if(isset($config['load'])){
            $this->beforeLoad($config['load']);
        }

        if(isset($config['beforeUnload'])){
            $this->beforeLoad($config['beforeUnload']);
        }

        if(isset($config['unload'])){
            $this->beforeLoad($config['unload']);
        }
    }
}