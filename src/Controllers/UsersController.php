<?php 
namespace App\Controllers;
use App\Controllers\Controller;
use App\App;
use App\Models\User;
use App\Auth\Auth;

/**
 * UsersController Class
 */
class UsersController extends Controller
{	


	public function index(App $app,$args=null){
		return $this->list($app,$args);
	}


	public function list(App $app,$args=null){

		$response = $app->db()->paginate('users',
			isset($args->page)? $args->page : 1 , 
			isset($args->ppage) ? $args->ppage : 10);


		if ($response){
			$data = array (
					 'title' => 'Users',
					 'galery' => 'users',
					 'users' =>  $response['data'] ,
					 'count' => $response['count'],
					 'page' => $response['page'],
					 'pages' => $response['pages'],
					 'icon' => 'fa fa-users'
					);

			}

		return $app->view('users/list', $data);

	}


	public function create_form(App $app,$args=null){

		$data = [
				'title' => 'New User',
				'icon' => 'fas fa-user-plus',
				 'type' => 'add',
				 'method' => 'POST',
				 'action' => '/users',
				];

		return $app->view('users/form', $data);
	}




	public function create(App $app,$args=null){
		$user = new User($app->request->data);
		$response = $user->create();
		$response->user = User::find('username',$app->request->data['username']);		
		return $response;
	}





	public function update(App $app,$args=null){

		$user = new User( (array) User::find('id', $app->request->data['id'])   );
		
		foreach( (array) $user as $key => $value ){
			unset($user->$key);
		}
	
		foreach ($app->request->data as $key => $value) {
			$user->$key = $value;
		}

		$response = $user->update();
		$app->response->set_log($response->msg,($response->status) ? 'success' : 'error');
		$response->user = User::find('id', $app->request->data['id']) ;

		return $response;

	}



	public function update_form (App $app,$args=null){
		$user = User::find('id', $args->id);
		if($user){
			$data = [ 'title' => 'Edit '.ucfirst($args->attr),
					   'icon' => 'fas fa-user-edit',
					   'action' => '/users/edit',
					  'type' => strtolower($args->attr),
					  'method' => 'POST',
					  'input' => ['id' => $args->id ],
					   ];
			$app->inputs($user);		   
			return $app->view('users/form', $data );
		}else{
			return $this->list($app,$args);
		}


	}



	public function delete(App $app,$args=null){
		$response = null; $user = User::find('id', $app->request->data['id']);
			if($user){
				$response = $user->delete();
			}

		return $response;	
	}





}


