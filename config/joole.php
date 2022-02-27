<?php

require_once '../web/core/ExampleClass.php';

return [
    'containers' => [
        'main' => [
            [
                'class' => \joole\reflector\Reflector::class,
                'depends' => [],
            ],
            [
                'class' => ExampleClass::class,
                'depends' => [\joole\reflector\Reflector::class],
                'params' => [

                ],
            ]
        ]
    ]
];