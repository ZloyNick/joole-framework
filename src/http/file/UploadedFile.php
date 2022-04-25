<?php

declare(strict_types=1);

namespace joole\framework\http\file;

use function move_uploaded_file, chmod, mkdir, array_key_last, explode;

/**
 * Uploaded file as object.
 */
final class UploadedFile
{

    public readonly bool $error;
    public readonly string $originalName;
    public readonly int $size;
    public readonly string $extension;
    public readonly string $tmp;

    final public function __construct(array $fileInfo)
    {
        $this->error = $fileInfo['error'];
        $this->originalName = $fileInfo['name'];
        $this->size = $fileInfo['size'];
        $this->extension = explode('/', $fileInfo['type'])[1];
        $this->tmp = $fileInfo['tmp_name'];
    }

    /**
     * Returns file size.
     *
     * @param bool $toString If true given, converts it to string (1.4 MB, 500 KB, ..., etc.).
     * @return int|string
     */
    final public function getSize(bool $toString = false): int|string
    {
        return $toString ? convertMemorySize($this->size) : $this->size;
    }

    /**
     * Returns file name from client.
     *
     * @return string
     */
    final public function getName(): string
    {
        return $this->originalName;
    }

    /**
     * Returns "/tmp/*" path of uploaded file.
     *
     * @return string
     */
    final public function getTmp(): string
    {
        return $this->tmp;
    }

    /**
     * Returns file extension.
     *
     * @return string
     */
    final public function getExtension(): string
    {
        return $this->extension;
    }

    /**
     * Saves file to given path.
     *
     * @param string $path
     * @return void
     */
    final public function saveAs(string $path): void
    {
        $pathParts = explode('/', $path);
        $path = $pathParts[array_key_last($pathParts)];

        mkdir($path, 0755);
        move_uploaded_file($this->tmp, $path);
        chmod($path, 0755);
    }

}