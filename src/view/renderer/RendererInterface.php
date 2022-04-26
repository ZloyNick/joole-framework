<?php

declare(strict_types=1);

namespace joole\framework\view\renderer;

use joole\framework\exception\view\RendererException;

/**
 * A basic renderer for files.
 *
 * Using for render content.
 */
interface RendererInterface
{

    /**
     * Prepares CSS content for view file.
     *
     * @param string $cssContent
     * @throws RendererException Will thrown when CSS content is empty.
     *
     */
    public function renderCssContent(string $cssContent): void;

    /**
     * Prepares JS content for view file.
     *
     * @param string $jsContent
     * @throws RendererException Will thrown when JS content is empty.
     *
     */
    public function renderJsContent(string $jsContent): void;

    /**
     * Renders view file.
     *
     * @param string $file Full path to file.
     *
     * @param array $params Options for view.
     * @return string
     *
     * @throws RendererException
     */
    public function renderFileContent(string $file, array $params = []): string;


}