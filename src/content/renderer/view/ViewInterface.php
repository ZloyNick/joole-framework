<?php

declare(strict_types=1);

namespace joole\framework\content\renderer\view;

use joole\framework\assets\ResourceInterface;
use joole\framework\content\renderer\RendererInterface;

/**
 * A basic view interface.
 *
 * View model using for control out content.
 */
interface ViewInterface
{

    /**
     * @param RendererInterface $renderer
     */
    public function __construct(RendererInterface $renderer);

    /**
     * Returns Renderer for view files.
     *
     * @return RendererInterface
     */
    public function getRenderer(): RendererInterface;

    /**
     * Returns combined content of view
     *
     * @return string
     */
    public function __toString(): string;

    /**
     * Includes given css file content.
     *
     * @param string $file File full path.
     *
     * @return static
     *
     * @throws \joole\framework\exception\view\ViewException Will thrown if file not exist.
     * @throws \joole\framework\exception\view\RendererException Will thrown on content render error.
     *
     */
    public function renderCssFile(string $file): static;

    /**
     * Includes given js file content.
     *
     * @param string $file File full path.
     *
     * @return static
     *
     * @throws \joole\framework\exception\view\ViewException Will thrown if file not exist.
     * @throws \joole\framework\exception\view\RendererException Will thrown on content render error.
     *
     */
    public function renderJsFile(string $file): static;

    /**
     * Includes given css content.
     *
     * @param string $cssContent CSS content
     *
     * @return static
     * @throws \joole\framework\exception\view\RendererException Will thrown on content render error.
     *
     */
    public function renderCss(string $cssContent): static;

    /**
     * Includes given js content.
     *
     * @param string $jsContent JS content
     *
     * @return static
     * @throws \joole\framework\exception\view\RendererException Will thrown on content render error.
     */
    public function renderJs(string $jsContent): static;

    /**
     * Applies resources.
     *
     * @param ResourceInterface ...$resources Array of resources.
     *
     * @return mixed
     */
    public function applyResources(ResourceInterface ...$resources): void;

    /**
     * Returns "View" object with set file.
     *
     * @param string $file
     * @return static
     */
    public function withFile(string $file): static;

    /**
     * Returns View instance with variables, that as used at given view file.
     *
     * @param array $params Array of variables for view.
     *
     * @return static
     */
    public function withParams(array $params):static;

}