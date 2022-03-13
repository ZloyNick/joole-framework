<?php

declare(strict_types=1);

namespace joole\framework\controller;

use joole\framework\http\BaseRequest;

class BaseController implements ControllerInterface
{

    protected BaseRequest $request;

    public function __construct()
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
}