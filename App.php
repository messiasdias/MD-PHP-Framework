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
	public $config, $maker_config, $request, $response, $routers=[], $args , $user;
	public $middleware_obj, $middleware_auth;
	private $root_dir;


	function __construct($config=null)
	{		
		@session_start();

		if( file_exists( getcwd().'/../config/app.php' ) ){
			include getcwd().'/../config/app.php'; //Load AppConfigs
			$this->set_config($config);
		}else{
			$maker = new Maker($this);
			$maker->file('config:app');
		}

		date_default_timezone_set($this->config->timezone);
		$this->request = new Request();
		$this->load_assets();  //Creting Sym link for ../assetes/public

		if(  explode('/', $this->request->url )[1] == 'api'  ){
			$this->config->mode = 'api';
			$this->request->url = str_replace('/api/' , '/', $this->request->url );
		}
		$this->response = new Response($this);
		return $this->load_router($this); //loading Routers files 
	}


	public function set_config($config=null){
	
		$this->config->path = getcwd().'/../';
		$this->config->vendor_path   = $this->config->path.'vendor/messiasdias/md-php-framework-lib/';
		$this->config->mode = 'app';
		$this->config->theme = '';
		$this->config->timezone = 'America/Recife';
		$this->config->views = $this->config->views.$this->config->theme;

		$config_array =  ( !is_null($config) && is_array($config) ) ? $config : $this->config;

		foreach($config_array as $key => $value ){
			if($key !== 'debug' ) $this->config->$key = $value;
		}
		
	}
	
	

	private function load_assets(){
		if (!file_exists($this->config->path.'public/assets')) {
			@symlink ($this->config->path.'assets/public', $this->config->path.'public/assets' );
		}
		return;
	}


	public function run(){

		$result = $this->get_callback($this->request->url, $this->request->method, $this->routers );
		$this->args = isset($result->route->args) ?  (object) $result->route->args : null;
		$this->response->set_http_code($result->code);

		 if ( isset($result->msg) ){
		 	 $this->response->set_http_msg($result->msg);
		  }
		 else { 
		 	$this->response->set_http_msg($this->response->get_http_msg($this->response->get_http_code()) );
		  }

		 $app = $this->middlewares( isset($result->route->middlewares) ? $result->route->middlewares : null, true);

		if ( $result->status &&  $app->middleware_auth  ){ 

			//Exec Callback Function of Route
		  $app = $result->route->callback($app, isset($result->route->args )? $result->route->args : null);

			if( !$app instanceof App ){

				$app = $this->view('http/500',
					['msg' => 'Return the var <i>App</i> $app end of router method!</h1> <p style="color:brown;">'.
					' Ex:<br> $app->get("/url/:param", $callback = function($app, $args)'.
					'{<b style="color: green"> return $app; </b> },[[string|Array] $middlewares | null] );'.
					' </p></center>' ]);
					exit;
			}
			

		}else{

			$file =  $this->config->path."assets/private/views/".$this->config->theme."/http/".$app->response->get_http_code().".html";

			if ( file_exists($file) && ( $app->response->get_http_code() != '200' ) ){
				
				$app =  $this->mode_trigger( 
						function ($app, $args, $result) {
							return $this->view('http/'.$app->response->get_http_code(),
							['page' => $app->request->url,
							'msg' => isset($result->msg) ? $result->msg : $app->response->get_http_msg() ]); 

						},function($app, $args, $result){

							return $this->json( array_merge( [ 'status' => (object)
							[ 'msg' => $app->response->get_http_msg(),
							'code' =>  $app->response->get_http_code()  ] ] )  );

						}, $result );
			

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
		
		if($app->config->debug_msg){
			echo '<div id="debug" class="debug">';
			echo '<h3>Debug --> $app->view_get_data() </h3>';	
			print_r($app->view_get_data());
			echo "</div>";
		}
		
		exit;
	}




	private function put_header(App $app ,$display_erro=false ){
		$response = $app->response;

		@header( $response->protocol.' '.$response->get_http_code().' '.$response->get_http_msg() );

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
	
		return $app;		

	}




	public function middlewares($list=null, $return_app=false){

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

		if($return_app){
			return $this; 
		}else{
			return $this->middleware_auth;
		}
		
	}
	

	public function middleware_verify($list=null) {
		if( !is_null($list) ) {
			$middleware = new Middleware($this, $list);
			return $middleware->verify();
		}
		return false;
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



	public function router_group(array $url, $callback=null, $middlewares=null){
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



	private function set_route($url, $callback, $middlewares, $method){

			if( is_array($url) ){
				foreach( $url as $url_key => $url_value ){
				 array_push($this->routers, new Route($url_value ,$method ? $method : 'GET' ,$callback,$middlewares) );
				}
			}elseif( is_string($url) ){
				array_push($this->routers, new Route($url,$method,$callback,$middlewares) );
			}else{
				return $this->response->set_http_code(500);
			}

	}	



	private function get_callback(string $url, $method, array $routers){		
		$router = new Router($routers);
		return $router->url($url, $method);
	}



	public function mode_trigger($app,$api,$data=null){
		if( $this->config->mode === 'app' ){
			return $app($this, $this->args, $data);
		}elseif( $this->config->mode === 'api' ){
			return $api($this, $this->args,$data);	
		}
	}



	private function load_router($app){
		//load routers app or api
		switch ( strtolower($app->config->mode) ) {
			case 'api':
				$mode = $this->config->path.'src/Routers/api/*.php';
			break;

			case 'app':
			default:
				$mode = $this->config->path.'src/Routers/*.php';
			break;
		}


		foreach ( glob($mode)  as $router_map ) {
			if (  file_exists($router_map) )
			{
				include $router_map;
			}
		}

		if ( $this->config->debug && file_exists($this->config->vendor_path.'/Maker/Routers.php') ){
			//Maker Routers
			include $this->config->vendor_path.'/Maker/Routers.php';
		}

		return $app;
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

	

	public function db(string $class = null ){
		if( !is_null($class) ){
			$class = (  !App::validate($class, 'startwith:App\\Models\\' ) ) ? 'App\\Models\\'.ucfirst($class) : ucfirst($class) ;
		}

		return new DB($class);
	}



	public function controller($name,$args=null){

		$method = ( count(explode('@', $name)) == 2 ) ? strtolower(explode('@', $name)[1]) : 'index';
		$class = 'App\Controllers\\'.ucfirst(explode('@', $name)[0] ); 

		if ( class_exists($class)) {
			$obj = new $class($this);
			if (method_exists($obj, $method)){
				return $obj->$method($this, $args);
			}else{
				$this->response->set_http_msg("Method '$method' not Found!");
			}
		}else {
			$this->response->set_http_msg("Class '$class'  not Found!" );
		}

		$this->response->set_http_code(500);
		return $this->view('http/500', ['page' => $this->request->url,
				 'msg' => $this->response->get_http_msg() ]);

	}




	public static function validate($data,$validations,$class=''){
		$validator = new Validator( $class );
		if(  is_array($data) && is_array($validations)  ) {
			return $validator->valid_array($data,$validations);
		}elseif( is_string((string) $data) &&  is_string((string) $validations)  ){
			return $validator->valid_string($data, $validations);
		}else{
			return false;
		}
	}




	public function view(string $name, array $data=null,string $path=null)
	{	
		$data = ((!is_null($data))) ? array_merge($data, (array) $this->view_get_data()) : (array) $this->view_get_data();
		$view = new View($this, !is_null($path) ? $path : $this->config->path.'assets/private/views/', $name, $data   );
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
			'referer'  => $this->request->referer,
			'host'  => $this->request->host,
			'scheme'  => $this->request->scheme,
			'request'  => $this->request,
			'response'  => $this->response,
			'user' => $this->user($this->request->token),
			'token' => ($this->request->token) ? $this->request->token : false,
			'input' => ($this->inputs()) ? $this->inputs() : false ,
			'assets' => '/assets/',
			'log' => ($this->response->get_log()) ? $this->response->get_log() : false,
			'debug' => $this->config->debug,
			'session' =>  ($_SESSION) ? ( (object) $_SESSION ) : false,
			'cookies' =>  ($this->request->cookies) ? ( (object) $this->request->cookies) : false,
			
		]; 

		$data =  array_merge($data, (array) $this->response->get_data() );
		$data = (object) array_merge($data, [ 'view_data' => json_encode($data) ] ) ;
		return (object) $data;
	}



	public function write(String $data , $type = 'html', $code=200, $msg = 'OK!')
	{	
		$this->response->write($data,$type,$code,$msg);
		return $this;
	}




	public function json($data, $code=200, $msg = 'Success!')
	{	
		$this->response->json($data,$code,$msg);
		return $this;
	}




	public function redirect_header($url){
		header('location:'.$url);
	}



	public function redirect($url, $method = "GET", $data=null){

		$this->request->url = strtolower( $url );
		$this->request->method = $method;
		
		if(!is_null($data)){
			$this->response->set_data($data);
		}
		
		$this->run();
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
			$this->response->set_log($response);
			$i++;
		}

		return $this;
	}


	public function download(string $filename ){
		$data = ['name' => pathinfo($filename)['basename'], 'path' =>  pathinfo($filename)['dirname'].'/' ];	
		$file = new File($data);
		$response = $file->download();
		$this->response->set_log($response);
		return $this;
	}


	


}

?>
