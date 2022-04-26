<?php

declare(strict_types=1);

namespace joole\framework\validator\http;

use joole\framework\http\response\BaseResponse;

/**
 * Interface RequestValidator
 *
 * Using for request validation.
 */
interface RequestValidatorInterface
{

    /**
     * Response for http request.
     *
     * @return BaseResponse
     */
    public function response(): BaseResponse;

}