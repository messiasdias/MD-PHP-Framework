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
		$this->name = strtolower($name);
		$this->method = strtolower($method);
		$this->callback = $callback;
		$this->middlewares = $middlewares;
	}

	public function getCallback(App &$app, array $args=null){
		$args = !is_null($args) ? (object) $args : false;
		return $this->callback->bindTo((object) ['app' => &$app, 'args' =>  $args ?? false] )();
	}	
}

