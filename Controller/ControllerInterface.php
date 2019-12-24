<?php

namespace App\Controller;
use App\App;


interface ControllerInterface {

	public function index(App $app, $args=null);


}
