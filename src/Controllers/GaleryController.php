<?php 
namespace App\Controllers;
use App\App;
use App\Controller\Controller;
use App\Database\DB;
/**
 * GaleryController Class
 */
class GaleryController extends Controller
{	public $app;
	


	public function index($app, $args=null){
		return $this->list_all($app,$args);
	}


	//search
	public function search(App $app,$args=null){
		$search = ['id', 'title','description','img','author_id',strtolower($app->request->data['search']) ];
		$data = $this->get_all($app, $args, $search);
		$data['search_action'] = strtolower('/'.$args->class.'s/search');

		return $app->mode_trigger( 
		function ($app, $args,$data) {
			return $app->view('adminlte/galery/list', $data);
		},function($app, $args, $data){
			return $app->json($data);
		}, $data);
		
	}

	//search2 (api return json data)
	public function search2($app, $args=null){
		$search = ['id', 'title','img','description','author_id',strtolower($app->request->data['search']) ];
		$itens = $this->get_all($app,$args,$search)['itens'];
		return isset($itens) ? $itens : array()  ;
	}

	

	public function search_author(App $app,$args=null){
		$search = ['id', 'title','description','author_id',strtolower($app->request->data['search']) ];
		$data['search_action'] = strtolower('/'.$args->class.'s/list/search');
		return $this->list_author($app, $args, $search);
	}



	//list
	public function list_all($app, $args=null, $search=null){
		$data = $this->get_all($app, $args, $search);
		$data['search_action'] = strtolower('/'.$args->class.'s/search');

		return $app->mode_trigger( 
		function ($app, $args,$data) {
			return $app->view('galery/galery', $data);
		},function($app, $args, $data){
			return $app->json($data);
		}, $data);
	}


	/*//list2
	public function list_all2($app, $args=null, $search=null){
		$data = $this->get_all($app, $args, $search)['itens'];
		foreach( $data as $i => $item ){
			if( !$item->is_publish() ){
				//unset($data[$i]);
			}
		}

		return $data;
	} */


	public function list_author($app, $args=null, $search=null){
	
		$data = $this->get_all($app, $args, $search);
		$itens_filted=[];
		$data['class'] = strtolower($args->class);

		if( isset($data['itens'])  ){
		  if( count($data['itens']) > 1 ) {	
			foreach( $data['itens'] as $i => $item ){
				if( $item->author_id == $app->user()->id ){
					$itens_filted[$i] =  $item;
				}
			}
		  }
		  else{
		  		$data['itens'] = is_array($data['itens']) ? $data['itens'][0] : $data['itens'] ;
				if( $data['itens']->author_id == $app->user()->id ){
					$itens_filted[0] = $data['itens'];	
				}
		  }
		
		$data['itens'] = $itens_filted;
		
		}else{
			unset($data['itens']);
		}

		$data['search_action'] = strtolower('/'.$args->class.'s/list/search');


		return $app->mode_trigger( 
		function ($app, $args,$data) {
			return $app->view('adminlte/galery/list', $data);
		},function($app, $args, $data){
			return $app->json($data);
		}, $data);

	}




	private function get_all($app, $args=null, $search=null){

		$db = new DB('App\\Models\\'.$args->class ); 
		$itens = $db->paginate( isset($args->page)? $args->page : 1 , isset($args->ppage) ? $args->ppage : 10, $search);
		$itens_filted=false; $i=0; 	$data=null;

		if($itens['data'] && is_array($itens['data'])  ) {
			foreach( $itens['data'] as $item ){
				$item = (object) $item;
				if(  ($item->publish == 1 ) | ( $app->user() &&  ( $item->author_id == $app->user()->id )   ) ){
					$itens_filted[$i] = $item;	
					$i++;
				} 
			}
		}else{
			$item = $itens['data'];

			if( $item ){

				if( ($item->publish == 1 ) | ( $app->user() &&  ( $item->author_id == $app->user()->id )  )   ){
					$itens_filted[0] = $item;	
				} 

			}
		}

		if ($itens_filted){

			$data = array (
					 'galery' => ucfirst($args->class).'s',
					 'itens' =>  $itens_filted ,
					 'count' => $itens['count'],
					 'page' => $itens['page'],
					 'pages' => $itens['pages'],
					 'icon' =>  ( strtolower($args->class) == "job" ) ? 'fa fa-briefcase' : 'fa fa-desktop' 
					);

		}

		$data['search_action'] = strtolower('/'.$args->class.'s/search');
		
		return $data;

	}


	


	public function save(App $app,$args=null){

		$class =  "App\\Models\\".ucfirst($args->class);
		$class_obj = new $class($app->request->data);
		$action = 'create';
		if( $class_obj->exists() ){
			$action = 'update';
			if ( $class_obj->author_id === 0 ) 	{
				$class_obj->author_name = 'Maker Sistema';
			}

		}else{
			$class_obj->author_id = $app->user()->id;
			$class_obj->author_name = $class_obj->author();
		}

		$result = $class_obj->$action();
		$app->response->set_log($result);

		return $app->mode_trigger( 
		function ($app, $args,$data) {
			$redirect =  (($args->type == 'add') && ($result->status == false) ) ? $args->class.'s/add' : $args->class.'s/edit/'.$class_obj->id;
			return $app->redirect($redirect);
		},function($app, $args, $data){
			return $app->json($data);
		}, $result);	

	}	



	public function delete(App $app,$args=null){
		$class =  "App\\Models\\".ucfirst($args->class);
		$class_obj = new $class($app->request->data);
		$result = $class_obj->delete();
		$app->response->set_log($result);

		
		

		return $app->mode_trigger( 
		function ($app, $args,$data) {
			return $app->redirect($app->request->data['redirect_url']);
		},function($app, $args, $data){
			return $app->json($data);
		}, $result);


	}




	public function img_upload($app, $args=null){
	

		$class = 'App\Models\\'.ucfirst($args->class);
		$filename = "../assets/public/img/galery/".$args->class."-".$app->request->data['id'].date("YmdHis").".".explode('.', $app->request->files['file']['name'])[1] ;
		$app = $app->upload($filename); $response = null;
		
		if( file_exists($filename) ){
			$item = $class::find('id', $app->request->data['id']);
				
			if( !strpos('/img/default/', $filename) ){
				@unlink(explode( '/img/', $filename )[0].$item->img );
			}

			$item->img = "/img/".explode( '/img/', $filename )[1];
			$response = $item->update();
			$app->response->set_log($response);
		}


		return $app->mode_trigger( 
		function ($app,$args,$response) {
			return $app->redirect( $app->request->referer );
		},function($app,$args,$response){
			return $app->json($response);
		}, $response);

	}






}


