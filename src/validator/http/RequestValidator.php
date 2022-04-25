<?php

declare(strict_types=1);

namespace joole\framework\validator\http;

use joole\framework\exception\http\response\BaseResponse;
use joole\framework\validator\Validator;

/**
 * The request validator base.
 */
abstract class RequestValidator extends Validator implements RequestValidatorInterface
{

    abstract public function response(): BaseResponse;

}