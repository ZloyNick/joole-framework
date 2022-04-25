<?php

declare(strict_types=1);

namespace joole\framework\controller;

use joole\framework\http\request\BaseRequest;
use joole\framework\view\BaseView;
use joole\framework\view\ViewInterface;

class BaseController implements ControllerInterface
{

    protected BaseRequest $request;
    private ViewInterface $view;

    public function __construct()
    {
        $this->request = request();
        $this->view = new BaseView();
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
     * Returns view of current action.
     *
     * @return ViewInterface
     */
    public function getView(): ViewInterface
    {
        return $this->view;
    }

    /**
     * Adds js code to view.
     *
     * @see ViewInterface::renderJs()
     *
     * @param string $jsContent
     * @throws \joole\framework\exception\view\RendererException
     */
    public function setJS(string $jsContent): void
    {
        $this->view->renderJs($jsContent);
    }

    /**
     * Adds css to view.
     *
     * @see ViewInterface::renderCss()
     *
     * @param string $cssContent
     * @throws \joole\framework\exception\view\RendererException
     */
    public function setCSS(string $cssContent): void
    {
        $this->view->renderCss($cssContent);
    }

    /**
     * Renders the view file.
     *
     * @param string $view
     * @param array $params
     *
     * @return string|ViewInterface
     *
     * @throws \joole\framework\exception\view\RendererException
     * @throws \joole\framework\exception\view\ViewException
     * @see ViewInterface::renderFile()
     *
     */
    public function render(string $view, array $params = []): string|ViewInterface
    {
        return $this->view->renderFile($view . '.php', $params);
    }

}