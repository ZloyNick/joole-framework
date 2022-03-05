<?php

declare(strict_types=1);

use joole\framework\data\container\Container;
use joole\framework\Joole;

use function scandir as scandir_native;

/** @noinspection PhpUnused */
const BASE_CONFIGURATION_PATH = __DIR__."/../config";

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
 * Analog of php:scandir, but without '.' and '..' elements.
 *
 * @param string $dir Directory.
 *
 * @return array Found elements. (Directories and files)
 */
function scan_dir(string $dir): array
{
    $elements = scandir_native($dir);
    // array_search is universal solution.
    // Indexes 0 and 1 are bad.
    $defect1 = array_search('.', $elements);
    $defect2 = array_search('..', $elements);

    if ($defect1 !== false) {
        unset($elements[$defect1]);
    }

    if ($defect2 !== false) {
        unset($elements[$defect2]);
    }

    return $elements;
}

function config(string $name, array|null $default = null):array|null{
    return Joole::$app->getConfig($name, $default);
}
