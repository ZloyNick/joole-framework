<?php

declare(strict_types=1);

namespace joole\framework\content\renderer;

use joole\framework\content\renderer\view\ViewInterface;
use joole\framework\exception\view\RendererException;

/**
 * A basic renderer for views.
 *
 * Using for render content.
 */
interface RendererInterface
{

    /**
     * @param string $viewsPath Default views path.
     */
    public function __construct(string $viewsPath);

    /**
     * Creates View object.
     *
     * @param string $file view's name or full file path.
     * @param string|null $namespace If null, will render file via using configuration property "views". If not null,
     * will render file from given sub path.
     * @param array $params Options for view.
     *
     * @return ViewInterface
     *
     * @throws RendererException
     */
    public function renderView(string $file, array $params = [], ?string $namespace = null): ViewInterface;

    /**
     * Returns cached content.
     *
     * @todo For future.
     *
     * @return string
     */
    // public function cachedContent():string;

    /**
     * Returns default views path.
     *
     * @return string
     */
    public function getViewsPath():string;

}