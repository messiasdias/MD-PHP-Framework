<?php
namespace App\Http;
use App\App;
use App\Http\Request;
use App\Auth\Token;
/**
 * Response Class
 */
class Response extends Request 
{	
	protected $headers=[], $http_code, $http_msg, $http_codes;
	public $view, $log=[], $app;
	
	function __construct( App $app)
	{
		foreach ($app->request as $key => $value) {
				$this->$key = $value;

			if ( (!is_null($key) && !is_null($value)) && !is_Array($value) ){
				$this->set_header_line($key, $value);
			}

		}
		$this->app = $app;
		$this->http_codes = $this->get_http_code_list();
	}





	public function set_http_code($code){

		if ( isset($code) ){
			 	$this->http_code = $code;
			  	$set_name=null;

			 	if ( is_null($this->http_msg) ){
			 		$set_name = $this->set_http_msg($this->http_codes[$code]);
			 	}

			 	if ($set_name && $this->get_http_code() ){
			 		return true;
			 	}
		} 
		return false;
	}




	public function get_http_code(){
		return (isset($this->http_code)) ? $this->http_code : false;
	}



	public function get_http_code_list(){
		return (array) json_decode( file_get_contents($this->app->config->vendor_path.'Http/http_codes.json') );
	}



	public function set_http_msg($msg){

		if ((isset($msg) && is_string($msg) ) ){
			$this->http_msg = ucwords($msg );
			return true;
		}
		return false;
		
	}



	public function get_http_msg($code=null){

		if ( !isset($this->http_msg) && is_null($code) ){
			return $this->http_msg;
		}else{
			
			if( $this->get_http_code() && is_null($code) ){
				return $this->http_codes[$this->get_http_code()];
			}elseif( !is_null($code) ){
				return $this->http_codes[$code];
			}

		}


		return false;
	}



	public function get_content_type(){
		isset($this->get_headers()['Content-Type'] )? $this->get_headers()['Content-Type'] : false;
	}



	public function set_content_type($content_type = 'html'){

		switch (strtolower($content_type) ) {
			
			default:
			case 'html':
				$content_type = 'text/html'; 
				break;

			case 'json':
				$content_type = 'application/json; charset=utf-8;'; 
				break;

			case 'text':
				$content_type = 'document'; 
				break;		
			
		}

		return $this->set_header_line('content_type', $content_type);
	}



	public function set_header_line(String $key, String $value){
		
		$key = str_replace(' ','-' ,ucwords(str_replace(['-', '_'],' ' , $key ))  ) ;

		if ( isset($key) && isset($value) ){
			 $this->headers[$key] = $value;

			 if (isset($this->headers[$key]) ){
			 	return true;
			 }

			 return false;
		} 

	}




	public function get_headers(){
		if( isset($this->headers)){
			return $this->headers;
		}
		return false;
	}



	public function json($data, $code=null, $msg=null ){
		$data = (object) $data;
		$this->set_content_type('json');
		
		if( !isset($data->token) ){	
			$data->token = isset($this->token) ? $this->app->auth()->token->renew($this->token) : false;
		}	
		$data->status = (object) ['code' => $this->get_http_code(), 'msg' => $this->get_http_msg()];

		$this->view = json_encode($data);
	}



	public function write(String $data , $content_type = 'html', $code=null, $msg=null ){
		$this->set_content_type($content_type);
		$this->set_http_code( !is_null($code) ? $code : $this->get_http_code() );
		$this->set_http_msg( !is_null($msg) ? $msg :  $this->get_http_msg($this->get_http_code()) );
		$this->view = $data;
	}



	public function view(){
		echo $this->view;
	}


	public function set_log( object $response, $class=null ){

			if( $response->status ){
				$class = 'success';
			}else{
				$class = is_null($class) ? 'warning' : $class ;
			}
	
	
			if( is_string($response->msg) ) {

				if( isset($this->log['msg']) ){
					$this->log['msg'][ count($this->log['msg']) ] = $response->msg;
				}else{
					$this->log['msg'][0] = $response->msg;
				}

			}elseif (is_array($response->msg)) {
				
				foreach ($response->$msg as $key => $value) {
					$this->log['msg'] .= "<br/>".$value;
				}

			}		
		
		$this->log['class'] = $class;
		return $this->log;
		
	}


	public function get_log(){
		return (!is_null($this->log)) ? (object) $this->log : false;
	}



	public function set_data($data){
		$this->data = $data;
	}



	public function get_data(){
		return (!is_null($this->data)) ? $this->data : false;
	}


}
