<?php
namespace App\Controller;
use App\App;
use App\Controller\ControllerInterface;
/**
 * Controller Class
 */
abstract class  Controller implements ControllerInterface
{	
	public $app;
	
	public function __construct(App $app=null)
	{
		$this->app = $app;
	}


	public abstract function index(App $app, $args=null);

	public function notfound(App $app, $args=null){
		$app->response->setCode(404);
		return $app->view('http/404', $args) ;
	}

	public function denied(App $app, $args=null){
		$app->response->setCode(401);
		return $app->view('http/401', $args) ;
	}

	public function error(App $app, $args=null){
		$app->response->setCode(500);
		return $app->view('http/500', $args) ;
	}



}