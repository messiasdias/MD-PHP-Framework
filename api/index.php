<?php

//include App
require_once "../vendor/autoload.php";
use App\App;

/* 
Start App using the argument 'app' for site, 
and with argument 'api'for API
--------------------------------------
Iniciar App usando o argumento 'app', 
Para API, o argumento 'api'.
*/

//Start App Api
$app = new App([ 'mode' => 'app', 'debug' => false]);
$app->run();
