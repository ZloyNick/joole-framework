<?php

declare(strict_types=1);

namespace joole\framework\view;

use joole\framework\assets\ResourceInterface;
use joole\framework\view\renderer\RendererInterface;

/**
 * A basic view interface.
 *
 * View model using for control out content.
 */
interface ViewInterface
{

    /**
     * Returns Renderer for view files.
     *
     * @return \joole\framework\view\renderer\RendererInterface
     */
    public function getRenderer(): RendererInterface;

    /**
     * Sets renderer.
     *
     * $rendererClass must be instance of \joole\framework\view\renderer\RendererInterface,
     * it's very important!
     *
     * @param RendererInterface|string $rendererClass Class of renderer as string or object
     * @throws \joole\framework\exception\view\ViewException Will thrown if given class as string
     * is not subclass of \joole\framework\view\renderer\RendererInterface
     *
     */
    public static function setRenderer(RendererInterface|string $rendererClass): void;

    /**
     * Renders given file content.
     *
     * @param string $file File full path.
     * @param array $params Variables for view.
     *
     * @return string
     *
     * @throws \joole\framework\exception\view\ViewException Will thrown if file not exist.
     * @throws \joole\framework\exception\view\RendererException Will thrown on content render error.
     */
    public function renderFile(string $file, array $params = []): string;

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

}