<?php
namespace App\Controllers;
use App\App;
use App\Controllers\Controller;

/**
 * TesteController Class
 */

class TesteController extends Controller
{	

	public function index($app, $args=null){
		$args['name'] = str_replace( '%20', ' ', $args['name']);
		return $app->view('teste', $args);
	}


}