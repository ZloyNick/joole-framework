<?php

declare(strict_types=1);

namespace joole\framework\view\renderer;

use joole\framework\exception\view\RendererException;

/**
 * Base renderer processing view content.
 */
class BaseRenderer implements RendererInterface
{

    private array $CSS = [];
    private array $JS = [];

    public function renderCssContent(string $cssContent): void
    {
        if (!trim($cssContent)) {
            throw new RendererException('An empty css content given.');
        }

        $this->CSS[] = $cssContent;
    }

    public function renderJsContent(string $jsContent): void
    {
        if (!trim($jsContent)) {
            throw new RendererException('An empty javascript content given.');
        }

        $this->JS[] = $jsContent;
    }

    public function renderFileContent(string $file, array $params = []): string
    {
        return sprintf("%s\n<style type=\"text/css\">%s</style>\n<script type=\"text/javascript\">%s</script>\n",
            self::getViewContent($file, $params),
            implode(PHP_EOL, $this->CSS),
            implode(PHP_EOL, $this->JS)
        );
    }

    /**
     * Returns view file content with php params using.
     *
     * @param string $file File, that will be rendered.
     * @param array $params php parameters.
     *
     * @return string Rendered content
     *
     * @throws RendererException
     */
    private static function getViewContent(string $file, array $params = []): string
    {
        if (!file_exists($file)) {
            throw new RendererException('File ' . $file . ' not found');
        }

        extract($params);
        ob_start();

        include_once($file);
        $content = ob_get_contents();

        ob_end_clean();

        return $content;
    }
}