<?php

/**
 *  Model Class
 */
namespace App\Models;
use App\App;
use App\Database\Validator;
use App\Database\DB;
use App\Models\ModelInterface;

class Model implements ModelInterface
{	
	
	public  $id, $created, $updated;

	/* Seting Table, if this not exists, create on construct Method*/
	public function __construct(array $data=[]){

		foreach ( $data as $key => $value) {

			if ( array_key_exists( $key, $data) && property_exists(get_called_class() , $key) ){
				$this->$key = $value;
			}

		}
	} 


	public static function all(array $paginate=null){
		$db = new DB();
		$table = strtolower( array_slice(explode('\\', strtolower(get_called_class().'s')), -1)[0] ) ;

		if ( !is_null($paginate) ){
		  return $db->paginate($table, $paginate[0], $paginate[1]);
		}

		return $db->select( self::table() , '*');
	}



	public static function find($col, $value){
		$db = new DB();
		return $db->select(self::table() , [strtolower($col), $value] );
	}


	private function clear($validations, $data=null){

		if( is_null($data) ){

			foreach ( (array) $this as $key => $value) {
				if ( isset($this->$key) ){
					$data[$key] = $this->$key ;
				}
			}
			
			if ( (isset($this->pass) && ( count_chars($this->pass) != 60 ))) {
				$data['pass']  = password_hash($this->pass, PASSWORD_BCRYPT);
			}			
		}
		

		if ( ( !is_null($data) && isset($data['pass']) ) &&  ( count_chars($data['pass']) != 60 ) ) {
			$data['pass'] = password_hash($data['pass'], PASSWORD_BCRYPT);
		}

		foreach ( $validations as $key => $value) {
			if ( !array_key_exists($key, $data) | !isset( $data[$key] ) ){
				unset($validations[$key])  ;
			}
		}

		foreach ( $data as $key => $value) {
			if ( !array_key_exists( $key, $validations) ){
				unset($data[$key])  ;
			}
		}

		return (object) [ 'data' => (array) $data, 'validations' => (array) $validations ];

	}



	public function table(){
		$table =  substr(get_called_class() ,strripos(get_called_class(), '\\')+1 , strlen(get_called_class())-1 );
		return strtolower($table).'s';
	}



	public function save(array $data, array $validations, $noexists = null){
		
		$clear = self::clear($validations, $data);
		$validate = App::validate($clear->data, $clear->validations);

		if(!$validate->errors){	
			
			foreach ($validate->data as $key => $value) {
				if ( preg_match('/^confirm_[a-zA-Z0-9]{1,}$/', $key) ){
					unset($clear->data[$key]);
				}
			} 	

			$db = new DB();
			return $db->save(self::table(), $clear->data);
				
		}else{
			return (object) [ 'status'=> false , 'msg' => "Error while Validating data!" , 'data'=> $validate->data, 'errors' => $validate->errors];
		} 


	}



	public function create (array $data=null){}
	public function update (array $data=null){}
	

	public function delete(array $data=null){

		if (is_string($data) && ( (int) $data >= 1 )){
			$data = ['id' => $data];
		}

		if( is_null($data) ){
			$data = ['id' => $this->id];
		}

		$validations = [
				'id' => 'int|mincount:1|exists:'.self::table(),
			];
		$clear = self::clear($validations, $data);	
		$validate = App::validate($clear->data, $clear->validations);

		if(!$validate->errors){	
			$db = new DB();
			return $db->delete(self::table(), $validate->data);
		}else {
			return (object) [ 'status'=> false , 'msg' => 'Error while Deleting object!' , 'data'=> $validate->data ];
		}

	}




}



?>