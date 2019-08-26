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
use App\Tools\Validator;
use App\Tools\View;
use App\Tools\File;
use App\Tools\File2;
use App\Database\DB;


class App
{
	public $request, $response, $routers=[], $args , $path ,$user, $mode, $theme, $timezone;


	function __construct($config=null)
	{	
		session_start();
		$this->set_config($config);
		date_default_timezone_set($this->timezone);
		$this->request = new Request();
		$this->response = new Response($this->request);
		//$this->back = unserialize( isset($_SESSION['back']) ? $_SESSION['back'] : null );
		include '../config/app.php'; //Load AppConfigs
		$this->load_assets();  //Creting Sym link for ../assetes/public
		return $this->load_router($this); //loading Routers files
	
	}


	public function set_config($config=null){

		$default_config['mode']= 'app';
		$default_config['theme'] = '';
		$default_config['path'] = '../src/';
		$default_config['debug'] = false;
		$default_config['timezone'] = 'America/Recife';

		foreach($default_config as $key => $value ){
			$this->$key = $value;	
		}	

		$config_array =  ( !is_null($config) && is_array($config) ) ? $config : $default_config;
	
		foreach($config_array as $key => $value ){
			$this->$key = $value;	
		}	

	}	


	public function run(){

		$result = $this->get_route($this->request->url, $this->request->method, $this->routers );
		$this->args = isset($result->route->args) ?  (object) $result->route->args : null;

		$this->response->set_http_code($result->code);

		 if ( isset($result->msg) ){
		 	 $this->response->set_http_msg($result->msg);
		  }
		 else { 
		 	$this->response->set_http_msg($this->response->get_http_msg($this->response->get_http_code()) );
		  }

		 $app = $this->middlewares( isset($result->route->middlewares) ? $result->route->middlewares : null);

		if ( $result->status &&  $app->middleware_auth  ){ 

			//Exec Callback Function of Route
		  $app = $result->route->callback($app, isset($result->route->args )? $result->route->args : null);

			if(!$app instanceof App ){

				$app = $this->view('http/500',
					['page' => $app->request->url,
					 'msg' => 'Return the var <i>App</i> $app end of router method!</h1> <p style="color:brown;">'.
					' Ex:<br> $app->get("/url/:param", $callback = function($app, $args)'.
					'{<b style="color: green"> return $app; </b> },[[string|Array] $middlewares | null] );'.
					' </p></center>' ]);
					exit;
			}
			


		}else{

			$file = "../assets/views/$this->theme/http/".$app->response->get_http_code().".html";

			if ( file_exists($file) && ( $app->response->get_http_code() != '200' ) ){
				
				$app = $this->view('http/'.$app->response->get_http_code(),
				 ['page' => $app->request->url,
				 'msg' => $app->response->get_http_msg() ]);

			}else{

				$app = $this->write( 
					'<div  style="color:#3333FF !important; height:100vh; width: 100wh; display: flex; flex-direction:column; justify-content: center; align-content: center; align-items:center; " >
					<h1  style="color:brown;" >Erro: '.$app->response->get_http_code().'</h1> '.
					$app->response->get_http_msg().'</h3></div>', //data writing
					'html',						 //type
					$app->response->get_http_code(), //reposnse code
					$app->response->get_http_msg() //reponse msg
				);

			}
			
		}

		$app = $this->put_header($app);
		$app->response->view();
		
		if(debug_msg){
			echo '<div id="debug" class="debug">';
			echo '<h3>Debug --> $app->view_get_data() </h3>';	
			print_r($app->view_get_data());
			echo "</div>";
		}
		
		exit;
	}




	private function put_header(App $app ,$display_erro=false){
		$response = $app->response;

		@header(  $response->protocol.' '.$response->get_http_code().' '.$response->get_http_msg() );

		if (  $response->cookies  ) {
			foreach ($response->cookies as $key => $value) {
				@setcookie( trim($key), trim($value) );
			}
		}

		
		if ($display_erro){
			echo $response->get_http_msg();
		}

		if(isset($response->token)){
			 //Renews Token
			 $renew_token = Token::renew($response->token);
			 if(isset($_SESSION['token']) && $app->user() ){
			 	$_SESSION['token'] = $renew_token;
			 }
			$response->token = $renew_token; 
			@header('token: '.$renew_token );
		}
	
		return $app;		

	}




	public function middlewares($list=null){

		$this->middleware_auth = true;

		if (!is_null($list)) {
			$middleware = new Middleware($this, $list);

			if( $middleware->verify() ){
				$this->middleware_auth = true;
			}else{
				$this->middleware_auth = false;
				$this->response->set_http_code(401);
				$this->response->set_http_msg('Access Denied!');
			}
		}

		return $this; 
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



	private function set_route($url, $callback, $middlewares, $method){
			array_push($this->routers, new Route($url,$method,$callback,$middlewares) );	
	}	



	private function get_route(string $url, $method,  array $routers){
		$router = new Router($routers);
		return $router->url($url, $method);
	}

	public function mode_trigger($app,$api,$data){
		if( $this->mode === 'app' ){
			return $app($this, $this->args, $data);
		}elseif( $this->mode === 'api' ){
			return $api($this, $this->args,$data);	
		}
	}



	private function load_router($app){

		//load routers app or api
		switch ( strtolower($app->mode) ) {
			case 'api':
				$mode = '../routers/api/*.php';
			break;

			case 'app':
			default:
				$mode = '../routers/*.php';
			break;
		}

		foreach ( glob($mode)  as $router_map ) {
			if (  file_exists($router_map) )
			{	
				include $router_map;
			}
		}

		if ($app->debug){
			//Maker Routers
			include '../src/Maker/Routers.php';
		}

		return $app;
	}




	private function load_assets(){

		if (!file_exists('../public/assets')) {
			//symlink ('../assets/public', '../public/assets' );
		}

		if (!file_exists('../api/assets')) {
			//symlink ('../assets/public', '../api/assets' );
		}

		return;
	}


	public function auth(){
		return new Auth($this);
	}


	public function user(){
		
		if( $this->request->token ) {	
			return $this->auth()->user($this->request->token);
		}
		return false;
	}


	public function db(){
		return new DB($this);
	}



	public function controller($name,$args=null){

		$method = ( count(explode('@', $name)) == 2 ) ? strtolower(explode('@', $name)[1]) : 'index';
		$name = ucfirst( str_replace(['Controller', 'controller'], [''],explode('@', $name)[0]) );
		$class = 'App\Controllers\\'.$name.'Controller'; 

		if ( class_exists($class)) {

			$obj = new $class($this);
			if (method_exists($obj, $method)){
				return $obj->$method($this, $args);
			}else{
				$this->response->set_http_code(500);
				$this->response->set_http_msg("Method $method no Found!");
				return $this->view('http/500', ['page' => $this->request->url,
				 'msg' => $this->response->get_http_msg() ]);

			}
		}

		$this->response->set_http_code(404);
		$this->response->set_http_msg('No Found!');
		return $this->view('http/404',  ['page' => $this->request->url,
		'msg' => $this->response->get_http_msg() ]);

	}




	public static function validate($data,$validations){
		$validator = new Validator();
		if(  is_array($data) && is_array($validations)  ) {
			return $validator->valid_array($data,$validations);
		}elseif( is_string((string) $data) &&  is_string((string) $validations)  ){
			return $validator->valid_string($data, $validations);
		}else{
			return false;
		}
	}




	public function view(string $name, array $data=null,string $path=views_dir)
	{	
		$data = ((!is_null($data))) ? array_merge($data, (array) $this->view_get_data()) : (array) $this->view_get_data();
		$view = new View($name, $data, $path);
		$this->response->write( $view->show(), 'html' );
		return $this;
	}



	public function inputs($inputs=null){

		if( is_null($inputs) ){
			return isset($this->request->data) ? $this->request->data : false;
		}else{
			$this->request->data = (object) $inputs;
			return ($this->request->data) ? true : false;
		}

	}



	public function view_get_data(){
		$data = [
			'url'  => $this->request->url,
			'user' => $this->user($this->request->token),
			'token' => ($this->request->token) ? $this->request->token : false,
			'input' => ($this->inputs()) ? $this->inputs() : false ,
			'assets' => '/assets/',
			'log' => ($this->response->get_log()) ? $this->response->get_log() : false,
			'app_description' => app_description,
			'debug' => $this->debug,
			'session' =>  ($_SESSION) ? ( (object) $_SESSION ) : false,
			'cookies' =>  ($this->request->cookies) ? ( (object) $this->request->cookies) : false,
		]; 

		$data = array_merge($data, (array) $this->response->get_data() );
		return (object) $data;
	}



	public function write(String $data , $type = 'html', $code=200, $msg = 'OK!')
	{	
		$this->response->write($data,$type,$code,$msg);
		return $this;
	}




	public function json($data, $code=302, $msg = 'Found!')
	{	
		$this->response->json($data,$code,$msg);
		return $this;
	}




	public function redirect_header($url){
		header('location:'.$url);
	}




	public function redirect($url, $method = "GET", $data=null){
		$this->request->url = strtolower($url);
		$this->request->method = $method;

		if( ($url === '/back') | ($url === 'back') ){
			$this->request->url = $this->back->url;
		}
		
		if(!is_null($data)){
			$this->response->set_data($data);
		}
		
		$this->run();
	}



	public function redirect_form_url(){
		if( isset($this->request->data['redirect']) ){
			$redirect = $this->request->data['redirect'];
			unset($this->request->data['redirect']);
			return $this->redirect_header($redirect);
		}else{
			return $this->back();
		}	
	}




	public function back(){
		return $this->redirect('/back');
	}




	public function file($file,array $args=null){
		return new File($file, $args);
	} 




	public static function api(string $url, string $method=null, array $data=null, string $form_type=null)
	{
			$client = new Client([ 'base_uri' => API , 
				"headers" => ['Token' => self::token()],
				'http_errors' => true
				 ]);
			$response = $client->request( strtoupper($method), $url , $data);
			echo $response->getStatusCode().'<br>';
			$response->getBody().'<br><br>';

	}



	public function upload(string $filename=null){

		$i=0;
		foreach ( $this->request->files as $key => $data ){
			
			if( !is_null($filename) && (count($this->request->files) == 1) ){
				$data['name'] = pathinfo($filename)['basename'] ;
				$data['path'] = pathinfo($filename)['dirname'].'/';
			}

			$file = new File2($data);
			$response = $file->upload() ;
			$this->response->set_log($response);
			$i++;
		}

		return $this;
	}


	public function download(string $filename ){
		$data = ['name' => pathinfo($filename)['basename'], 'path' =>  pathinfo($filename)['dirname'].'/' ];	
		$file = new File2($data);
		$response = $file->download();
		$this->response->set_log($response);
		return $this;
	}


	


}

?>