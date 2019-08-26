<?php 
namespace App\Controllers;
use App\Controllers\Controller;
use App\App;

/**
 * AuthController Class
 */
class AuthController extends Controller
{


	public function index(App $app, $args=null){
		return  $app->view(  ( $app->user() ) ? 'dashboard' : 'login' , ['user' => $app->user()] );
	}


	public function login(App $app, $args=null){
		
		$response = $app->auth()->login($app->request->data);
		return $app->mode_trigger( 
			function ($app, $args,$response) {
			$app->response->set_log($response);
			return $app->redirect_header('/admin');
		},function($app, $args, $response){
			return $app->json($response->data);
		}, $response);

	}


	public function logout(App $app, $args=null){
		$response = $app->auth()->logout();
		return $app->mode_trigger( 
			function ($app, $args,$response) {
			return $app->redirect_header('/admin');
		}, function ($app, $args,$response){
			return $app->json([]);
		}, $response);
	
	}


}


