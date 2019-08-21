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
		$result = null;

		if($app->request->data) {
			$result = $app->auth()->login($app->request->data);
		}
	
		if ( $result->status ){
			$app->response->set_log($result->msg,'success');
			return $app->redirect_header('/admin');
		}else{
			$app->response->set_log($result->msg,'error');
			return $app->redirect('/admin');
		}

	}


	public function logout(App $app, $args=null){
		$app->auth()->logout();
		$app->redirect_header('/admin');
	}


}


