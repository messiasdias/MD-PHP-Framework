<?php
namespace App\Database;
use App\App;
/**
 *  Database Seeder Class
 */
class Seeder 
{
	private $spoon_flag, $seeder_objects, $response = [];		

	function __construct(App $app){
		if( file_exists($app->config->path.'/config/maker.php' ) ){
			include $app->config->path.'/config/maker.php';
		}
	}

	public function set_response( Object $rs , string $item_name=''){
	
		if ( isset( $rs->status) && $rs->status ) {		
			array_push($this->response, 'Create '.$item_name.' Successfully!');
		}else{
			array_push($this->response, 'Erros while creating the '.$item_name.'!');

			if ( isset($rs->errors) && $rs->errors) {
				foreach ($rs->errors as $key => $value) {
					array_push($this->response, 'Error: '.$key.' | '.$value) ;
				}
			}	
		}

	}

	public function get_response(){
		return $this->response;
	}
	
}