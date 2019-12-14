<?php
namespace App\Database;

/**
 *  Database Seeder Class
 */
class Seeder 
{
	protected $response;		

	function __construct(){}

	public function set_response($rs , string $msg=''){
	
		if ( isset( $rs->status) && $rs->status ) {		
						$this->response .= '<br><b style="color:green;">Create '.$msg.' Successfully!</b>';
			}else{
				    	$this->response .=  '<br><b style="color:brown;">Erros while creating the '.$msg.'!</b>';

				    	if ( isset($rs->errors) && $rs->errors) {
				    		foreach ($rs->errors as $key => $value) {
				    			$this->response .= '<p  style="color:brown;" >Error: '.$key.' | '.$value.'</p>' ;
				    		}
				    	}	
			}

	}

	public function get_response(){
		return $this->response;
	}
	
}