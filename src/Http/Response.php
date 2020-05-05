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
	private $headers=[], $http_code, $http_msg, $http_codes;
	public $view, $log=[], $app;
	
	function __construct( App $app)
	{
		foreach ($app->request as $key => $value) {
				$this->$key = $value;

			if ( (!is_null($key) && !is_null($value)) && !is_Array($value) ){
				$this->setHeaderLine($key, $value);
			}

		}
		$this->app = $app;
		$this->http_codes = $this->getCodeList();
	}


	public function setCode($code){

		if ( isset($code) ){
			 	$this->http_code = $code;
			  	$set_name=null;

			 	if ( is_null($this->http_msg) ){
			 		$set_name = $this->setMsg($this->http_codes[$code]);
			 	}

			 	if ($set_name && $this->getCode() ){
			 		return true;
			 	}
		} 
		return false;
	}




	public function getCode(){
		return (isset($this->http_code)) ? $this->http_code : false;
	}



	public function getCodeList(){
		return (array) json_decode( file_get_contents($this->app->config->vendor_path.'Http/http_codes.json') );
	}



	public function setMsg($msg){

		if ((isset($msg) && is_string($msg) ) ){
			$this->http_msg = ucwords($msg );
			return true;
		}
		return false;
		
	}



	public function getMsg($code=null){

		if ( !isset($this->http_msg) && is_null($code) ){
			return $this->http_msg;
		}else{
			
			if( $this->getCode() && is_null($code) ){
				return $this->http_codes[$this->getCode()];
			}elseif( !is_null($code) ){
				return $this->http_codes[$code];
			}

		}


		return false;
	}



	public function getContentType(){
		isset($this->getHeaders()['Content-Type'] )? $this->getHeaders()['Content-Type'] : false;
	}



	public function setContentType($content_type = 'html'){

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

		return $this->setHeaderLine('content_type', $content_type);
	}



	public function setHeaderLine(String $key, String $value){
		
		$key = str_replace(' ','-' ,ucwords(str_replace(['-', '_'],' ' , $key ))  ) ;

		if ( isset($key) && isset($value) ){
			 $this->headers[$key] = $value;

			 if (isset($this->headers[$key]) ){
			 	return true;
			 }

			 return false;
		} 

	}




	public function getHeaders(){
		if( isset($this->headers)){
			return $this->headers;
		}
		return false;
	}



	public function json($data, $code=null, $msg=null ){
		$data = (object) $data;
		$this->setContentType('json');
		
		if( !isset($data->token) ){	
			$data->token = isset($this->token) ? $this->app->auth()->token->renew($this->token) : false;
		}	

		$data->status = (object) ['code' => $this->getCode(), 'msg' => $this->getMsg()];
		$this->view = json_encode($data);
	}



	public function write(String $data , $content_type = 'html', $code=false, $msg=false ){
		$this->setContentType($content_type);
		$this->setCode( $code ?? $this->getCode() );
		$this->setMsg(  $msg ??  $this->getMsg($this->getCode()) );
		$this->view = $data;
	}



	public function view(){
		echo $this->view;
	}


	public function setLog( object $response, $class=null ){

		$log = ['msg' => '', 'class' => ''];	
		$log['msg'] = $response->msg;

		if( $response->status ){
			$log['class'] = !is_null($class) ? $class : 'success';		
		}else{
			$log['class'] = !is_null($class) ? $class : 'error';	
		}

		if( isset($response->errors) && $response->errors){
			$log['errors'] = [];
			foreach( $response->errors as $key =>  $error){
				$log['errors'][$key] = (object) [ "msg" => $error[0], "class" => isset($error[1]) ?  $error[1] : "error"  ] ;
			}
		}

		$this->log =  $log;	
		return $this->log;
	}


	public function getLog(){
		return (!is_null($this->log)) ? (object) $this->log : false;
	}



	public function setData($data){
		$this->data = $data;
	}



	public function getData(){
		return (!is_null($this->data)) ? $this->data : false;
	}


}
