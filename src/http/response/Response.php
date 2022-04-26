<?php

declare(strict_types=1);

namespace joole\framework\http\response;


class Response extends BaseResponse
{

    public function asJson(array $data, int $code = 200): static
    {
        $this->withHeader('Content-Type', ['application/json; charset=utf-8']);

        $this->code = $code;
        $this->content = json_encode($data);

        return $this;
    }

}