<?php

declare(strict_types=1);

namespace joole\framework\view;


use joole\framework\assets\ResourceInterface;
use joole\framework\exception\view\ViewException;
use joole\framework\view\renderer\BaseRenderer;
use joole\framework\view\renderer\RendererInterface;

class BaseView implements ViewInterface
{

    /**
     * The renderer.
     *
     * @var RendererInterface
     */
    private static RendererInterface $renderer;

    public function __construct()
    {
        self::$renderer = new BaseRenderer();
    }

    public function getRenderer(): RendererInterface
    {
        return self::$renderer;
    }

    public static function setRenderer(RendererInterface|string $rendererClass): void
    {
        if (is_string($rendererClass)) {
            if (is_subclass_of($rendererClass, RendererInterface::class)) {
                throw new ViewException('New renderer class must be instance of ' . RendererInterface::class);
            }

            self::$renderer = new $rendererClass();
        }

        self::$renderer = $rendererClass;
    }

    public function renderFile(string $file, array $params = []): string
    {
        $file = app()->getConfig('app')['views'] . $file;

        return self::$renderer->renderFileContent($file, $params);
    }

    public function renderCssFile(string $file): static
    {
        if (!is_file($file)) {
            throw new ViewException('File ' . $file . ' not found');
        }

        $stream = fopen($file, 'r+');
        $streamContent = fread($stream, filesize($file));

        self::$renderer->renderCssContent($streamContent);

        return $this;
    }

    public function renderJsFile(string $file): static
    {
        if (!is_file($file)) {
            throw new ViewException('File ' . $file . ' not found');
        }

        $stream = fopen($file, 'r+');
        $streamContent = fread($stream, filesize($file));

        self::$renderer->renderCssContent($streamContent);

        return $this;
    }

    public function renderCss(string $cssContent): static
    {
        self::$renderer->renderCssContent($cssContent);

        return $this;
    }

    public function renderJs(string $jsContent): static
    {
        self::$renderer->renderJsContent($jsContent);

        return $this;
    }

    public function applyResources(ResourceInterface ...$resources): void
    {
        // TODO: Implement applyResources() method.
    }
}