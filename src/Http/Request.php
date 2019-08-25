<?php
namespace App\Http;
/**
 * Request Class
 */
class Request 
{	
	public $host, $remote_host, $url, $cookies, $protocol, $method, $content_type, $data, $files, $connection, $cache_control, $upgrade_insecure_requests, $user_agent, $accept, $accept_encoding, $accept_language, $token;
	
	function __construct()
	{
		//var_dump( explode( '/', $_SERVER['REQUEST_URI'])[0] );

		$this->url = strtolower($_SERVER['REQUEST_URI']);
		$this->protocol = $_SERVER['SERVER_PROTOCOL'];
		$this->method =  $_SERVER['REQUEST_METHOD'];
		$this->content_type = isset($_SERVER["CONTENT_TYPE"])? $_SERVER["CONTENT_TYPE"]: 'text/html';

		//Get All Form data posts
   		parse_str(file_get_contents('php://input'), $this->data );
   		
   		//Get User Auth Token 
   		if(isset( $this->data['token'] )) {
   			$this->token =  $this->data['token'];
   			unset($this->data['token']);
   		}
   		elseif(isset( $_SESSION['token'] )){
   			$this->token = $_SESSION['token'];
   		}

   		if ( isset($_FILES['file'] )  ){

			if(  is_array( $_FILES['file']['name'] ) ){
				//multi-files
				foreach( $_FILES['file'] as $key1 => $value1 ){
					foreach( $value1 as $key2 => $value2 ){
						$this->files[$key2][$key1] = $value2;
					}
				}
			}else{
				//sigle file	
				$this->files = $_FILES;
			}
	

   		}

		foreach (apache_request_headers() as $key => $value) {
			$param = strtolower( str_replace('-', '_', $key) );

				if ($param == 'cookie'){
					$this->cookies = $this->cookie_to_array($value);
				}elseif( property_exists($this, $param) ) {
					$this->$key = $value;
				}

		}

	}



	public function cookie_to_array(string $param){
		
		if ( !is_null($param) && is_string($param) ) {
				
				$param_array=[];

				if ( strrpos( $param, ';') ) {
					$exp1 =  explode(';', $param);

					foreach ($exp1 as $exp_v1) {

						if (strrpos( $exp_v1 , '=' )){
							
							$exp2 =  explode('=', $exp_v1);
						 	$param_array[$exp2[0]] = $exp2[1]; 
						 	
						}else{

							array_push($param_array , $exp_v1 );
							
						}
					}
					

				}
					
				return $param_array;	
			
		}

		return $param;
	}







}