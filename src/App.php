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
use Symfony\Component\Dotenv\Dotenv;



class App
{
	public $config, $maker_config, $request, $response, $routes=[], $args, $user;
	public $middleware_obj, $middleware_auth;



	function __construct()
	{		
		@session_start();
		$this->set_paths();
		$this->setEnv();
		$this->request = new Request();
		$this->response = new Response($this);
		$this->config->views = $this->config->path->root.'src/Views/templates/';
		date_default_timezone_set($this->getEnv()->app_timezone);
		return $this;
	}


	private function set_paths(){
		@$this->config->path = (object) [
			'root' => getcwd()."/../",
			'vendor' => __DIR__.'/'
		];
	}

	public static function getEnvFile(){
		$env_file = getcwd().'/../.env';

		if( file_exists($env_file) ){
			return $env_file;
		}
		elseif( file_exists($env_file.'.local') ){
			return $env_file.'.local';
		}else{
			return false;
		}
	}

	public static function setEnv(){
		$dotenv = new Dotenv();
		$env_file = self::getEnvFile();

		if( $env_file ){
			$dotenv->load($env_file);
		}
		else{
			echo "File .env or .env.local Not Found!";
			exit; 
		}
	}


	public static function getEnv(){
		$envs = [];	
		if( count($_ENV) >= 1 ){
			foreach( explode(',',$_ENV["SYMFONY_DOTENV_VARS"]) as  $name ){
				if(!is_null($_ENV[$name])) {
					$envs[strtolower($name)] = $_ENV[$name] ;
				}
			}
		}
		return (object)	 $envs; 
	}



	public function run(){

		$routing = $this->routing();
		$this->args = isset($routing->route->args) ?  (object) $routing->route->args : null;
		$this->response->setCode($routing->code);

		if ( isset($routing->msg) ){
			$this->response->setMsg($routing->msg);
		}
		else { 
			$this->response->setMsg($this->response->getMsg($this->response->getCode()) );
		}

		$this->middlewares( isset($routing->route->middlewares) ? $routing->route->middlewares : null, null, true);
		
		if ( $routing->status &&  $this->middleware_auth  ){ 
			//Exec Callback Function of Route
		  	$routing->route->getCallback($this, $routing->route->args ?? null );
		}else{

			$text = '<div  style="color:#9e7700 !important; height:100vh; width: 100wh; display:flex; flex-direction:column; justify-content: center; align-content: center; align-items:center;">'.
			'<h1 style="color:#696969;">Erro: {{http.code}}</h1><p>{{http.msg}}</p></div>';

			$file =  $this->config->views."/http/".$this->response->getCode().".html";
			
			if( !file_exists($file) ){
				$file = $this->config->views."/layout/http.html";	
			}

			if( !file_exists($file) ){
				$file = $this->config->views."/http.html";	
			}

			$data = [
					'code' => $this->response->getCode(),
					'msg' =>  $this->response->getMsg(),
					'url' => $this->request->url,
					'text' => $text,
					'file' => $file
			];

			$this->mode_trigger( 
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

		$this->setHeader();
		$this->response->view();
		exit;
	}




	private function setHeader(){

		@header( $this->response->protocol.' '.$this->response->getCode().' '.$this->response->getMsg() );

		if (  $this->response->cookies  ) {
			foreach ($this->response->cookies as $key => $value) {
				@setcookie( trim($key), trim($value) );
			}
		}

		if(isset($this->response->access_token)){
			 //Renews Token
			 $token = new Token($this);
			 $renew_token = $token->renew($this->response->access_token);
			 if(isset($_SESSION['access_token']) && $this->user() ){
				 $_SESSION['access_token'] = $renew_token;
				 $this->response->token = $renew_token;
			 }
			$this->response->access_token = $renew_token; 
			@header('access_token: '.$renew_token );
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
		}

		if( !$this->middleware_auth && $denyAcess) {
			$this->response->setCode(401);
			$this->middleware_auth = false;
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

	public function path($url, $callback, $middlewares=null){
		$this->set_route($url, $callback, $middlewares,'PATCH');
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



	private function load_routes(App &$app){
		//load routes app or api
		switch ( strtolower($this->getEnv()->app_mode) ) {
			case 'api':
				$mode = $this->config->path->root.'src/Routes/api/*.php';
			break;
			case 'app':
			default:
				$mode = $this->config->path->root.'src/Routes/*.php';
			break;
		}

		foreach ( glob($mode)  as $map ) {
			
			if (  file_exists($map) )
			{
				include $map;
			}
		}

		//Maker Routes
		if ( ($this->getEnv()->app_env == 'dev') && file_exists($this->config->path->vendor.'/Maker/Routes.php') ){
			include $this->config->path->vendor.'/Maker/Routes.php';
		}
	}	


	public function routing(string $url=null, string $method=null){

		if( $this->getEnv()->app_api && explode('/', $this->request->url )[1] == 'api'  ){
			$this->getEnv()->app_mode = 'api';
			$this->request->url = str_replace('/api/' , '/', $this->request->url );
		}

		$this->load_routes($this);
		$router = new Router($this->routes);
		return $router->url( $url ?? $this->request->url, $method ?? $this->request->method);
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
		return new Auth();
	}


	public function user(){
		if( isset($this->request->access_token) ) {	
			return $this->auth()->user($this->request->access_token);
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
			$this->response->setMsg("Class '$class' not Found!" );
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



	public function write(String $data , $type = 'html', $code=200, $msg = 'OK, Working as expected.')
	{	
		$this->response->write($data,$type,$code,$msg);
		return $this;
	}



	public function view(string $name, array $data=null, string $path=null)
	{	
		$view = new View($this, is_null($path) ? $this->config->views : $path , $name, $data );
		$this->response->write( $view->show(), 'html' );
		return $this;
	}


	public function json($data, $code=200, $msg = 'OK, Working as expected.')
	{	
		$this->response->json($data,$code,$msg);
		return $this;
	}



	public function mode_trigger($app,$api,$data=null){
		if( $this->getEnv()->app_mode === 'app' ){
			return $app($this, $this->args, $data);
		}elseif( $this->getEnv()->app_mode === 'api' ){
			return $api($this, $this->args,$data);	
		}
	}



	public function api(string $url, string $method='GET', array $data=[])
	{	
		//config no verbose http fatal errors
		$data['http_errors'] = false;

		$client = new \GuzzleHttp\Client();
		//@ temporario
		$response = @$client->request(
			strtoupper($method),
			$url,
			$data,
		);

		return (object) [
		'status' => $response->getStatusCode() ,
		'content_type' => $response->getHeaderLine('content-type') , 
		'data' => json_decode($response->getBody()) ];
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
