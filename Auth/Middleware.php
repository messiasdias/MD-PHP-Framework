<?php
namespace App\Auth;
use App\App;
/**
 * Middleware Class
 */
class Middleware
{
	private $app, $list, $middlewares;	
	

	public function __construct(App $app, $list = 'guest'){
		$this->app = $app;
		
		if( file_exists($this->app->path.'config/middlewares.php') ){
			include $this->app->path.'config/middlewares.php'; //Load middlewares
		}else{
			echo "File ".$this->app->path."config/middlewares.php not Found!";
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

		}elseif(is_array($middleware_list)) {
			$array_list = array_map('trim', $list );
		}

		return $array_list;

	}



	public function verify(){

		foreach( $this->list as $name  ){
			if( isset($this->middlewares->$name) ) {
				$middleware = $this->middlewares->$name;
				if($middleware($this->app)){
					return true;
				}
			}
		}
		
		return false;
	
	}








}

