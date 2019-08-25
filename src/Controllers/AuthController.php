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

		//var_dump($app->auth()->login($app->request->data)); exit;

		$app->response->set_log($app->auth()->login($app->request->data));
		return $app->redirect_header('/admin');
	}


	public function logout(App $app, $args=null){
		$app->auth()->logout();
		$app->redirect_header('/admin');
	}


}


