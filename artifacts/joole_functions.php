<?php

declare(strict_types=1);

use joole\framework\Application;
use joole\framework\data\container\Container;
use joole\framework\exception\http\response\BaseResponse;
use joole\framework\http\request\Request;
use joole\framework\Joole;

/** @noinspection PhpUnused */
const BASE_CONFIGURATION_PATH = __DIR__ . "/../config";

/**
 * Returns container by name.
 *
 * @param string $name Container's name
 * @return Container|null
 */
function container(string $name): ?Container
{
    return Joole::getContainer($name);
}

/**
 * Returns config.
 *
 * @param string $name
 * @param array|null $default
 * @return array|null
 */
function config(string $name, array|null $default = null): array|null
{
    return app()->getConfig($name, $default);
}

/**
 * Returns an application's instance.
 *
 * @return Application
 */
function app(): Application
{
    return Joole::$app;
}

/**
 * Returns client's request as object.
 *
 * @return Request
 */
function request(): Request
{
    return app()->request;
}

/**
 * Returns response as object.
 *
 * @return BaseResponse
 */
function response(): BaseResponse{
    return app()->response;
}

/**
 * Converts memory size to string.
 *
 * @param int $bytesCount
 * @return string
 */
function convertMemorySize(int $bytesCount):string{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];

    $bytes = max($bytesCount, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);

    return round($bytes, 2) . ' ' . $units[$pow];
}