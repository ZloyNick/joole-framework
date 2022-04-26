<?php

/*
 * Configuration file for Joole class.
 */
return [
    'components' => [
        [
            'name' => 'router',
            'class' => \joole\framework\routing\BaseRouter::class,
            'options' => [],
            'routes' => __DIR__ . '/routes/',
        ],
    ],
];