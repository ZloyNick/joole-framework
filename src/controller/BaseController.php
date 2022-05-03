<?php

declare(strict_types=1);

namespace joole\framework\controller;

use joole\framework\http\request\BaseRequest;
use joole\framework\http\response\BaseResponse;
use joole\framework\routing\ActionInterface;

/**
 * The basic controller.
 *
 * @property \joole\framework\routing\BaseAction $action
 */
class BaseController implements ControllerInterface
{

    protected BaseRequest $request;
    protected array $_additionalCss = [];
    protected array $_additionalJs = [];

    public function __construct(protected readonly ActionInterface $action)
    {
        $this->request = request();
    }

    public function beforeAction(string $method): void
    {
    }

    public function afterAction(string $method): void
    {
    }

    /**
     * Returns request of controller.
     *
     * @return BaseRequest
     */
    public function getRequest(): BaseRequest
    {
        return $this->request;
    }

    /**
     * Adds js code to view.
     *
     * @param string $jsContent
     * @throws \joole\framework\exception\view\RendererException
     * @see ViewInterface::renderJs()
     *
     */
    public function setJS(string $jsContent): void
    {
        $this->_additionalJs[] = $jsContent;
    }

    /**
     * Adds css to view.
     *
     * @param string $cssContent
     * @throws \joole\framework\exception\view\RendererException
     * @see ViewInterface::renderCss()
     *
     */
    public function setCSS(string $cssContent): void
    {
        $this->_additionalCss[] = $cssContent;
    }

    /**
     * Renders the view file.
     *
     * @param string $view
     * @param array $params
     *
     * @return string
     *
     * @throws \joole\framework\exception\view\RendererException
     *
     * @see ViewInterface::renderFile()
     */
    public function render(string $view, array $params = []): BaseResponse
    {
        $viewObject = app()->getRenderer()->renderView($view.'.php', $params);

        $viewObject->renderJs(implode(PHP_EOL, $this->_additionalJs));
        $viewObject->renderCss(implode(PHP_EOL, $this->_additionalCss));

        return response()->withOutput(
            $viewObject->__toString()
        );
    }

}