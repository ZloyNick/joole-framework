<?php

/*
 * Configuration file for Joole class.
 */
return [
    'containers' => [
        'main' => [
            [
                'class' => \joole\reflector\Reflector::class,
                'depends' => [
//                    [
//                        'class' => \joole\reflector\Reflector::class,
//                        'owner' => 'main',
//                    ]
                ],
//                'params' => [],
            ]
        ],
    ]
];