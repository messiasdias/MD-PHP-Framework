<?php
namespace App\Http;
use App\App;
/**
 * Route Class
 */
class Route
{	
	public $name, $method, $middlewares;
	private $callback;
	
	function __construct($name,$method,$callback,$middlewares)
	{
		$this->name = $name;
		$this->method = $method;
		$this->callback = $callback;
		$this->middlewares = $middlewares;
	}


	public function callback(App $app, array $args=null){
		 $function = $this->callback;
		 return $function($app, (object) $args);
	}	
}

