<?php

/**
 * App Class
 */

namespace App;


use App\Http\Request;
use App\Http\Response;
use App\Http\Route;
use App\Http\Router;
use App\Auth\Token;
use App\Auth\Middleware;
use App\Auth\Auth;
use App\Others\Validator;
use App\View\View;
use App\Others\File;
use App\Database\DB;
use App\Database\Table;
use App\Maker\Maker;



class App
{
	public $config, $maker_config, $request, $response, $routes=[], $args, $user;
	public $middleware_obj, $middleware_auth;


	function __construct($config=null)
	{		
		@session_start();
		$this->set_paths();
		$this->request = new Request();
		$this->response = new Response($this);
		$this->set_config($config);
		return $this;
	}


	private function set_paths(){
		$this->config = (object) [];
		$this->config->path = getcwd()."/../";
		$this->config->vendor_path = __DIR__.'/';
	}


	private function set_config($config=null)
	{	
		$this->config->mode = 'app';
		$this->config->api = true;
		$this->config->debug = true;
		$this->config->timezone = "America/Recife";
		$this->config->views = $this->config->path.'assets/private/views/';
		$config_array =  ( !is_null($config) && is_array($config) ) ? $config : $this->config;
		
		foreach($config_array as $key => $value ){
			if($key !== 'debug' ) $this->config->$key = $value;
		}

		if( file_exists( $this->config->path.'config/app.php' ) ){
			include $this->config->path.'config/app.php'; //Load AppConfigs
		}else{
			$this->response->setLog((object)[ 
					  'msg' => "File /config/app.php Not Found!",
					  'status' => false
					], 'error'); 
		}
		
		date_default_timezone_set($this->config->timezone);
		$this->load_assets();  //Creting Sym link for ../assetes/public
	}
	
	

	private function load_assets(){
		if (!file_exists($this->config->path.'public/assets')) {
			$this->response->setLog((object)[ 
				'msg' => "Shortcut 'assets/' not found in /public/ !",
				'status' => false
			  ], 'error'); 

			@symlink ($this->config->path.'assets/public', $this->config->path.'public/assets' );
			
			if (file_exists($this->config->path.'public/assets')){
				$this->response->setLog((object)[ 
					'msg' => "Shortcut 'assets/' in /public/ created successfully!",
					'status' => false
				  ], 'success'); 
			}
		}
	}



	public function run(){
		$app = null;

		$routing = $this->routing();
		$this->args = isset($routing->route->args) ?  (object) $routing->route->args : null;
		$this->response->setCode($routing->code);

		if ( isset($routing->msg) ){
			$this->response->setMsg($routing->msg);
		}
		else { 
			$this->response->setMsg($this->response->getMsg($this->response->getCode()) );
		}

		$this->middlewares( isset($routing->route->middlewares) ? $routing->route->middlewares : null,null,true);
		
		if ( $routing->status &&  $this->middleware_auth  ){ 
			//Exec Callback Function of Route
		  	$routing->route->callback($this, isset($routing->route->args )? $routing->route->args : null);
		}else{

			$text = '<div  style="color:#9e7700 !important; height:100vh; width: 100wh; display:flex; flex-direction:column; justify-content: center; align-content: center; align-items:center;">'.
			'<h1 style="color:#696969;">Erro: {{code}}</h1><p>{{msg}}</p></div>';

			$file =  $this->config->views."/http/".$this->response->getCode().".html";
			
			if( !file_exists($file) ){
				$file = $this->config->views."/layout/http.html";	
			}

			$data = [
					'code' => $this->response->getCode(),
					'msg' =>  $this->response->getMsg(),
					'url' => $this->request->url,
					'text' => $text,
					'file' => $file
			];

			$app =  $this->mode_trigger( 
				function ($app, $args, $data) {
					return file_exists($data['file']) ? 
						$app->view('layout/http', ['code' => $data['code'],'msg' => $data['msg'],]) : 
						$app->write( str_replace(['{{code}}', '{{msg}}'],[ $data['code'], $data['msg']], $data['text'])) ; 
				},
				function($app, $args, $data){
					return $app->json([]);
				}, 
				$data 
			);
			
		}

		$this->put_header($this);
		$this->response->view();
		exit;
	}




	private function put_header(App &$app ,$display_erro=false ){
		$response = $app->response;

		@header( $response->protocol.' '.$response->getCode().' '.$response->getMsg() );

		if (  $response->cookies  ) {
			foreach ($response->cookies as $key => $value) {
				@setcookie( trim($key), trim($value) );
			}
		}
	
		if ($display_erro){
			echo $response->getMsg();
		}

		if(isset($response->token)){
			 //Renews Token
			 $token = new Token($this);
			 $renew_token = $token->renew($response->token);
			 if(isset($_SESSION['token']) && $app->user() ){
				 $_SESSION['token'] = $renew_token;
				 $response->token = $renew_token;
			 }
			$response->token = $renew_token; 
			@header('token: '.$renew_token );
		}

		@header("Access-Control-Allow-Origin: *");
		@header("Access-Control-Allow-Headers: Content-Type");	

	}




	public function middlewares($list=null, object $obj=null, $denyAcess=false){

		$this->middleware_auth = true;
		if (!is_null($list)) {
			$this->middleware_obj = !is_null($obj) ? $obj : null;
			$middleware = new Middleware($this, $list);
			$this->middleware_auth = $middleware->verify();

			if($denyAcess) {
				$this->response->setCode(401);
				$this->response->setMsg('Access Denied!');
			}
		
		}
		
		return $this->middleware_auth;
	}
	


	public function get($url, $callback, $middlewares=null){
		$this->set_route($url, $callback, $middlewares,'GET');
	}


	public function post($url, $callback, $middlewares=null){
		$this->set_route($url, $callback, $middlewares,'POST');
	}


	public function put($url, $callback, $middlewares=null){
		$this->set_route($url, $callback, $middlewares,'PUT');
	}

	public function delete($url,$callback, $middlewares=null){
		$this->set_route($url, $callback, $middlewares,'DELETE');
	}

	public function group(array $url, $callback=null, $middlewares=null){
		return $this->router_group($url, $callback, $middlewares);
	}

	public function route_group(array $url, $callback=null, $middlewares=null){
		return $this->router_group($url, $callback, $middlewares);
	}

	public function router_group(array $url, $callback=null, $middlewares=null){

		if( is_null($callback) ){
			$callback = function ($app, $args){
				$this->response->setCode(500);
				return $this->view('layout/msg', ['title' => 'Callback no is Defined',
				 'subtitle' => "Function callback no is Defined on route ".$app->request->url."!" ]);
			};
		}

		foreach( $url as $url_key => $url_value ){
			if( isset($url_value['url'] )  ){
				if( is_array( $url_value['url'] ) ){
					foreach( $url_value['url'] as $url_value_item ){
						$this->set_route($url_value_item, isset($url_value['callback'] ) ? $url_value['callback'] : $callback,  isset($url_value['middlewares'] ) ? $url_value['middlewares'] :  $middlewares, isset($url_value['method'] ) ?  $url_value['method'] : 'GET' );
					}
				}else{
					$this->set_route($url_value['url'], isset($url_value['callback'] ) ? $url_value['callback'] : $callback,  isset($url_value['middlewares'] ) ? $url_value['middlewares'] :  $middlewares, isset($url_value['method'] ) ?  $url_value['method'] : 'GET' );
				}
			}elseif( is_string($url_value) ) {
				$this->set_route($url_value, $callback, $middlewares,'GET');
			}
		}
	}



	private function load_routes(){
		//load routes app or api
		$app = $this;
		switch ( strtolower($this->config->mode) ) {
			case 'api':
				$mode = $this->config->path.'src/Routes/api/*.php';
			break;
			case 'app':
			default:
				$mode = $this->config->path.'src/Routes/*.php';
			break;
		}

		foreach ( glob($mode)  as $map ) {
			
			if (  file_exists($map) )
			{
				include $map;
			}
		}

		if ( $this->config->debug && file_exists($this->config->vendor_path.'/Maker/Routes.php') ){
			//Maker Routes
			include $this->config->vendor_path.'/Maker/Routes.php';
		}

		return $app;
	}


	private function set_route(string $url, $callback, $middlewares, string $method){

			if( is_array($url) ){
				foreach( $url as $url_key => $url_value ){
				 array_push($this->routes, new Route($url_value ,$method ? $method : 'GET' ,$callback,$middlewares) );
				}
			}elseif( is_string($url) ){
				array_push($this->routes, new Route($url,$method,$callback,$middlewares) );
			}else{
				return $this->response->setCode(500);
			}

	}	


	public function routing(string $url=null, string $method=null){

		if( $this->config->api && explode('/', $this->request->url )[1] == 'api'  ){
			$this->config->mode = 'api';
			$this->request->url = str_replace('/api/' , '/', $this->request->url );
		}

		$this->load_routes();
		$router = new Router($this->routes);
		return $router->url(!is_null($url) ? $url : $this->request->url,
		 !is_null($method) ? $method : $this->request->method);
	}



	public function redirect_header($url)
	{
		header('Location:'.$url, true, 301);
		exit;
	}



	public function redirect($url, $method = "GET", $data=null)
	{
		$this->request->url = strtolower( $url );
		$this->request->method = strtoupper($method);
		
		if(!is_null($data)){
			$this->inputs($data);
		}

		$this->run();
	}



	public function auth(){
		return new Auth($this);
	}


	public function user(){
		if( isset($this->request->token) ) {	
			return $this->auth()->user($this->request->token);
		}
		return false;
	}

	

	public static function db(string $class = null ){
		if( !is_null($class) ){
			$class = (  !App::validate($class, 'startwith:App\\Models\\' ) ) ? 'App\\Models\\'.ucfirst($class) : ucfirst($class) ;
		}
		return new DB($class);
	}


	public static function validate($data,$validations,$class='')
	{
		$validator = new Validator( $class );
		if(  is_array($data) && is_array($validations)  ) {
			return $validator->valid_array($data,$validations);
		}elseif( is_string((string) $data) &&  is_string((string) $validations)  ){
			return $validator->valid_string($data, $validations);
		}else{
			return false;
		}
	}




	public function controller($name,$args=null)
	{
		$method = ( count(explode('@', $name)) == 2 ) ? strtolower(explode('@', $name)[1]) : 'index';
		$class = 'App\Controllers\\'.ucfirst(explode('@', $name)[0] ); 

		if ( class_exists($class)) {
			$obj = new $class($this);
			if (method_exists($obj, $method)){
				return $obj->$method($this, !is_null($args)? $args : $this->args);
			}else{
				$this->response->setMsg("Method '$method' not Found!");
			}
		}else {
			$this->response->setMsg("Class '$class'  not Found!" );
		}

		$this->response->setCode(500);
		return $this->view('layout/msg', ['title' => $this->request->url,
				 'subtitle' => $this->response->getMsg() ]);

	}




	public function inputs(array $inputs=null)
	{
		if( is_null($inputs) ){
			return isset($this->request->data) ? $this->request->data : false;
		}else{
			$this->request->data = (object) $inputs;
			return ($this->request->data) ? true : false;
		}
	}



	private function view_get_data()
	{	
		$data = [
			'description' => (  $this->config &&  $this->config->description) ? 
			 $this->config->description : '',
			'url'  => $this->request->url,
			'referer'  => $this->request->referer,
			'host'  => $this->request->host,
			'scheme'  => $this->request->scheme,
			'request'  => $this->request,
			'response'  => $this->response,
			'user' => $this->user($this->request->token),
			'token' => ($this->request->token) ? $this->request->token : false,
			'input' => ($this->inputs()) ? $this->inputs() : false ,
			'assets' => '/assets/',
			'log' => isset($this->response) ? (array) $this->response->getLog() : false,
			'debug' => isset($this->config) ? $this->config->debug: true,
			'session' =>  ($_SESSION) ? ( (object) $_SESSION ) : false,
			'cookies' =>  ($this->request->cookies) ? ( (object) $this->request->cookies) : false,
		]; 

		$data =  array_merge($data, $this->response ?  (array) $this->response->getData() : [] );
		$data = (object) array_merge($data, [ 'view_data' => json_encode($data) ] ) ;
		return (object) $data;
	}


	public function write(String $data , $type = 'html', $code=200, $msg = 'OK!')
	{	
		$this->response->write($data,$type,$code,$msg);
		return $this;
	}


	public function view(string $name, array $data=null, string $path=null)
	{	
		$data = ((!is_null($data))) ? array_merge($data, (array) $this->view_get_data()) : (array) $this->view_get_data();
		$path = is_null($path) ? $this->config->views : $path;
		$view = new View($this, $path , $name, $data );
		$this->response->write( $view->show(), 'html' );
		return $this;
	}


	public function json($data, $code=200, $msg = 'Success!')
	{	
		$this->response->json($data,$code,$msg);
		return $this;
	}



	public function mode_trigger($app,$api,$data=null){
		if( $this->config->mode === 'app' ){
			return $app($this, $this->args, $data);
		}elseif( $this->config->mode === 'api' ){
			return $api($this, $this->args,$data);	
		}
	}




	public function api(string $url, string $method='GET', array $data=[])
	{
		$client = new \GuzzleHttp\Client();
		$response = $client->request( strtoupper($method) , $url, $data);
		$status =  $response->getStatusCode(); # 200
		$content_type = $response->getHeaderLine('content-type'); # 'application/json; charset=utf8'
		$data = (object) json_decode($response->getBody() ) ; # '{"id": 1420053, "name": "guzzle", ...}'
		return (object) ['status' => $status, 'content_type' => $content_type, 'data' => $data ];
	}	



	public function upload(string $filename=null){

		$i=0;
		foreach ( $this->request->files as $key => $data ){
			
			if( !is_null($filename) && (count($this->request->files) == 1) ){
				$data['name'] = pathinfo($filename)['basename'] ;
				$data['path'] = pathinfo($filename)['dirname'].'/';
			}

			$file = new File($data);
			$response = $file->upload() ;
			$this->response->setLog($response);
			$i++;
		}

		return $this;
	}


	public function download(string $filename ){
		$data = ['name' => pathinfo($filename)['basename'], 'path' =>  pathinfo($filename)['dirname'].'/' ];	
		$file = new File($data);
		$response = $file->download();
		$this->response->setLog($response);
		return $this;
	}



}// end App

?>
