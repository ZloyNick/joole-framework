<?php

use joole\framework\Joole;

require_once '../vendor/autoload.php';
ini_set('display_errors', 'on');
error_reporting(E_ALL);

class App extends \joole\framework\Application{

}

Joole::build(new App())->run();