<?php

declare(strict_types=1);

namespace joole\framework\validator\http;

use joole\framework\http\response\BaseResponse;
use joole\framework\validator\Validator;

/**
 * The request validator base.
 */
class RequestValidator extends Validator implements RequestValidatorInterface
{

    public function response(): BaseResponse{
        return response()->asJson(
            $errors = $this->getErrors(),
            !empty($errors) ? 200 : 422
        );
    }

}