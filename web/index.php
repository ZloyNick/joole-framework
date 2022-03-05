<?php

use joole\framework\Joole;

require_once '../vendor/autoload.php';

class App extends \joole\framework\Application{

}

Joole::build(new App())->run();