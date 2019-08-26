<?php 
namespace App\Controllers;
use App\Controllers\Controller;
use App\App;
/**
 * GaleryController Class
 */
class GaleryController extends Controller
{	public $app;
	
	function __construct(App $app=null)
	{
		$this->app = $app;
	}


	public function index($app, $args=null){
		return $this->list($app,$args);
	}

	public function list($app, $args=null){
		
		$itens = $app->db()->paginate( strtolower($args->class).'s' ,isset($args->page)? $args->page : 1 , 
		isset($args->ppage) ? $args->ppage : 10);

		if ($itens){
			$data = array (
					 'galery' => ucfirst($args->class).'s',
					 'itens' =>  $itens['data'] ,
					 'count' => $itens['count'],
					 'page' => $itens['page'],
					 'pages' => $itens['pages'],
					 'icon' => 'briefcase'
					);

			}

		return $app->view('painel/layout/dashboard:painel/galery/galery_list', $data);
	}



	public function create_form(App $app,$args=null){
		$data = [
				'title' => 'New '. ucfirst($args->class),
				'icon' => ( ucfirst($args->class) == 'Job') ? 'fas fa-briefcase' : 'fa fa-desktop',
				'type' => 'add',
				'method' => 'POST',
				'class' => strtolower($args->class),
				'action' => '/'.strtolower($args->class).'s/add',
				];

		return $app->view('galery/form', $data);
	
	}




	public function edit_form(App $app,$args=null){

		$class = 'App\Models\\'.ucfirst($args->class);
		$class_object = $class::find('id', $args->id);

		$data = [
				'title' => 'Edit '. ucfirst($args->class),
				'icon' => ( ucfirst($args->class) == 'Job') ? 'fas fa-briefcase' : 'fa fa-desktop',
				'type' => 'edit',
				'method' => 'POST',
				'class' => strtolower($args->class),
				'action' => '/'.strtolower($args->class).'s/edit',
				];
	    $app->inputs($class_object);		
		return $app->view('galery/form', $data);
	}




	public function create(App $app,$args=null){
		$class =  "App\\Models\\".ucfirst($args->class);
		$class_obj = new $class($app->request->data);
		$result = $class_obj->create();
		$data = [
				'title' => (($result->status) ? 'Edit' : 'New').' '. ucfirst($args->class),
				'icon' => ( ucfirst($args->class) == 'Job') ? 'fas fa-briefcase' : 'fa fa-desktop', 
				'action' => '/'.strtolower($args->class).'s/add',
				 'type'  => ($result->status) ? 'edit' : 'add',
				 'method' => 'POST',
				 'class' => strtolower($args->class),
				 'errors' => isset($result->errors) ? $result->errors : false
				];
		$app->response->set_log($result->msg,($result->status) ? 'success' : 'error');
		return $app->view('galery/form', $data);
	}




	public function update(App $app,$args=null){

		$class =  "App\\Models\\".ucfirst($args->class);
		$class_obj = new $class($app->request->data);
		$result = $class_obj->update();
		$data = [
			 	'action' => '/'.strtolower($args->class).'s/'.($result->status) ? 'edit' : 'add',
				'type'  => ($result->status) ? 'edit' : 'add',
				 'method' => 'POST',
				 'class' => strtolower($args->class),
				 'errors' => isset($result->errors) ? $result->errors : false
				];
		$app->response->set_log($result->msg,($result->status) ? 'success' : 'error');
		return $app->view('galery/form', $data);

	}



	
	public function delete(App $app,$args=null){
		$class =  "App\\Models\\".ucfirst($args->class);
		$class_obj = new $class($app->request->data);
		$result = $class_obj->delete();
		return $app->redirect('/'.$args->class.'s');
	}




}


