<?php
namespace App\Database;

/**
 *  Database Seeder Class
 */
class Seeder 
{
	protected $response = [];		

	function __construct(){}

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