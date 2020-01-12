<?php
namespace App\Http;
use App\App;

/**
 * Router Class
 */

class Router 
{    
	private $routers, $method;
	
	function __construct(array $routers)
	{	
		//Set $routers
		$this->routers = $routers;
	}


	//return  (object) [ 'verify'=> true, 'code'=> 200, 'route' => (object) $route];
	public function url($url, $method = 'GET'){

		//set url
		$this->url = $url;
		//set method
		$this->method = $method;	

		$routers = $this->find_by_name($this->url);
		$routers = ($routers) ? $routers : $this->find_by_regex($this->url);

		if ( $routers &&  ( count($routers) > 1) )
		{
			return (object) [ 'status' => false, 'code' => 500, 'route' => null, 'msg' => 'Replicate routes, '.count($routers).' Routes with the same name or signature for '.$this->url.'.' ];
		}
		elseif( $routers &&  ( count($routers) == 1) )
		{
			return (object) [ 'status' => true, 'code' => 200, 'route' => $routers[0] ];
		}
		else{
			return (object) [ 'status' => false, 'code' => 404, 'route' => null ];
		}

	}




	private function find_by_regex($url=null){

		$url = is_null($url) ? $this->url : $url;
		$url_exp = array_filter(explode('/',$url )) ;
		$routers=null; $i=0;
		$data=[]; $continue=false;
		$url_start_with ='';

		foreach ($this->find_by_method( $url, $this->find_by_count() ) as $route_key => $route) 
		{
			 $route_name_exp =  array_filter(explode("/", $route->name));

			 foreach( $route_name_exp as $route_name_exp_key => $route_name_exp_value){


				if( isset($url_exp[$route_name_exp_key]) && ($route_name_exp_value ===  $url_exp[$route_name_exp_key])  ) {

					$routers[$i]= $route;
					$url_start_with .= '/'.$route_name_exp_value;

					if( ($route_name_exp_key === count($url_exp) ) && ( $url === $url_start_with) ){
						return [$route];
					}else{
						$continue = true;
					}

				}
				elseif( $continue && (count(explode('}', $route_name_exp_value) ) >= 2 )){
						
						$arg_exp = explode('}', $route_name_exp_value);
						$arg_regex = (count($arg_exp) > 1)? $arg_exp[1] : '' ;
						$arg_id =  (count($arg_exp) > 1)? str_replace('{', '', $arg_exp[0]) : null ;

						if(  App::validate( $url_exp[$route_name_exp_key] , $arg_regex)){
							
							$url_start_with .= '/'.$url_exp[$route_name_exp_key];
							$route->args[$arg_id] = $url_exp[$route_name_exp_key];
							$routers[$i]= $route;

							if( ($route_name_exp_key === count($url_exp)) && ( $url === $url_start_with) ){
								return [$route];
							}
						}else{
							$continue=false;
						}

				}else{
					$continue = false;
				}
				

			}

			$url_start_with='';
			$i++;
		}

		return  false;
		
	}



	private function find_by_name($url=null){

		$url = is_null($url) ? $this->url : $url;

		if ( (strlen($url)>1) && (strripos($url,'/') == (strlen($url)-1)) ){
			$url = substr($url,0, strlen($url)-1 );
		}

		$routers=[]; $i=0;
		foreach ( $this->find_by_method( $url, $this->find_by_count() ) as $route) {
			if( ((count( array_filter( explode('/', $url ) ) ) == count( array_filter(explode('/', $route->name ) ) )) && ($this->method == $route->method )) && ($url == $route->name) ){
				$routers[$i] = $route;
			 	$i++;
			}
		}	

		return $routers;

	}




	private function find_by_method($url=null,$routers=null){

		$routers = is_null($routers) ? (array) $this->routers : $routers;
		$i=0; $return=[];
		if ($routers) {
			foreach ($routers as $route) 
			{	
				if( $route->method === $this->method ){
					$return[$i] = $route;
				}
				$i++;
			}
		}

		return $return;
	}




	private function find_by_count($url=null,$routers=null){
		
		$url = preg_replace('/^\/\s*/', '',  is_null($url) ? $this->url : $url );
		$routers = is_null($routers) ? $this->routers : $routers;
		$i=0; $return=[];

		if ($routers) {	
			foreach ($routers as $route) 
			{	
				if( ( count( array_filter(explode('/', $route->name)) ) === count( array_filter(explode('/',$url )) )) ){
					$return[$i] = $route;
				}
				$i++;
			}
	   }

		return $return;
	}





}