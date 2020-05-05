<?php
namespace App\Auth;
use App\App;
use App\Maker\Maker;

/**
 * Middleware Class
 */
class Middleware
{
	private $app, $list, $middlewares;	
	

	public function __construct(App $app, $list = 'guest'){
		$this->app = $app;
		
		if( file_exists($this->app->config->path.'config/middlewares.php') ){
			include $this->app->config->path.'config/middlewares.php'; //Load middlewares
		}else{
			$maker = new Maker($app);
			$maker->file('config:middlewares');
		}

		$this->list = $this->listtoarray($list);
	}



	private function listtoarray($list){
		$array_list = [];

		if ( is_string($list) ){

			if ( strpos($list, ',')  ){
				foreach (explode(',', $list) as $value) {
					array_push($array_list , trim($value," ") );
				}
			}
			elseif( strpos($list, '|')  ){
				foreach (explode('|', $list) as $value) {
					array_push($array_list , trim($value," ") );
				}
			}	
			else{
				array_push($array_list , trim($list," ") );
			}

		}elseif(is_array($list)) {
			$array_list = array_map('trim', $list );
		}

		return $array_list;

	}



	public function verify(){

		foreach( $this->list as $name  ){
			if( isset($this->middlewares->$name) ) {
				$middleware = $this->middlewares->$name;
				if ( $middleware($this->app, (object) $this->app->middleware_obj ) ) {
					return true;
				}
			}
		}
		
		return false;
	}








}

