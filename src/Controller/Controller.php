<?php
namespace App\Controller;
use App\App;
use App\Controller\ControllerInterface;
/**
 * Controller Class
 */
abstract class  Controller implements ControllerInterface
{	

	public abstract function index(App $app, $args=null);

	public function notfound(App $app, $args=null){
		return $app->response->setCode(404);
	}

	public function denied(App $app, $args=null){
		return $app->response->setCode(401);
	}

	public function error(App $app, $args=null){
		return $app->response->setCode(500);
	}

}