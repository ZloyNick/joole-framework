<?php

declare(strict_types=1);

namespace joole\framework\http\response;

use joole\framework\exception\http\FileNotFoundException;
use function filesize;
use function fopen;
use function fpassthru;

class Response extends BaseResponse
{

    /**
     * Returns response as json.
     *
     * @param array $data
     * @param int $code
     * @return $this
     */
    public function asJson(array $data, int $code = 200): static
    {
        $this->withHeader('Content-Type', ['application/json; charset=utf-8']);

        $this->code = $code;
        $this->content = json_encode($data);

        return $this;
    }

    /**
     * Returns file content.
     *
     * @throws FileNotFoundException
     */
    public function asFile(string $filePath): static
    {
        $file = fopen($filePath, 'rb');

        if (!$file) {
            throw new FileNotFoundException('Failed to open file "' . $filePath . '"');
        }

        response()
            ->withHeader('Content-Type', 'image/png')
            ->withHeader('Content-Length', filesize($filePath));
        fpassthru($file);

        exit(200);
    }

}