<?php
namespace App\Http;
use App\App;
/**
 * Route Class
 */
class Route
{	
	public $name, $method, $middlewares, $args;
	private $callback;
	
	function __construct($name,$method,$callback,$middlewares)
	{
		$this->name = $name;
		$this->method = strtoupper($method);
		$this->callback = $callback;
		$this->middlewares = $middlewares;
	}

	public function callback(App $app, array $args=null){
		 $function = $this->callback;
		 //$this->args = $args;
		 return $function($app, (object) $args);
	}	
}

