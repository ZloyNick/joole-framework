<?php

use joole\framework\Joole;

require_once '../vendor/autoload.php';

$joole = new Joole();
$config = require_once '../config/joole.php';
$joole->init($config);

var_dump(Joole::getContainer('main')->get(ExampleClass::class));