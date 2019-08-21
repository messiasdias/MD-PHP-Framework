<?php

namespace App\Controllers;
use App\App;


interface ControllerInterface {

	public function index(App $app, $args=null);


}
