<?php
namespace App\Controllers;
use App\App;
use App\Controllers\ControllerInterface;
/**
 * Controller Class
 */
class Controller implements ControllerInterface
{	
	public $app;
	
	public function __construct(App $app=null)
	{
		$this->app = $app;
	}


	public function index(App $app, $args=null){
		
	}



}