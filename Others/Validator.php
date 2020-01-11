<?php
namespace App\Others;
use App\Database\DB;
use App\App;
use App\Database\Table;
/**
 * Validator Class
 */
class Validator
{	
	public $valid, $data, $class, $errors=[];

	public function __construct(string $class=null){
		$this->valid = false;
		$this->class = (!is_null($class) && class_exists($class) )? $class : false;
	}

	public function valid_string(string $data, string $validations){
		$this->data = $data ? $data : null;
		$valid =  $this->valid_array([ 'value' => $data], ['value' => $validations]);
		if(!$valid->errors){
			return true;
		}
		
		return false;
	}
	
	public function valid_array(array $data, array $validations){
		 $datav=null; $errors=false; $this->data = $data;
		foreach ($validations as $key => $value) {
			foreach( explode('|', $value ) as $index => $regex ){
				$regex_key =  explode(':',$regex )[0];
				$regex_val =  isset(explode(':',$regex )[1]) ? explode(':',$regex )[1] : NULL;
				$datav[$key][$regex_key] = $this->exec_method($regex_key, [$data[$key],$regex_val, $key] )  ;
			}

		}

		foreach ($datav as $key => $value){
			foreach($value as $key2 => $value2 ){
				if( isset($value2->valid)  && !$value2->valid){
					if(!isset($errors[$key]) ) {
						$errors[$key] = $value2->error;
					}else{
						$errors[$key] .= ' | '.$value2->error;
					}
				}
			}
			
		}

		return (object) ['data'=> $data, 'errors' => $errors];
	
	}


	public function exec_method($method, $arg){
		if ( method_exists($this,$method)){				
			return (object) $this->$method($arg) ;
		}else{
			return false;
		}		
	}


	private function int($arg){

		if ( filter_var( is_array($arg) ? $arg[0]: $arg , FILTER_VALIDATE_INT) ) {
			return  (object) [ 'valid' => true];
		}else {
			return  (object)  ['valid' => false, 'error' => 'Not is a valid Integer value!'] ;
		}
	}


	private function integer($arg){
		return $this->int(is_array($arg) ? $arg[0]: $arg);
	}



	private function float($value){
		if (filter_var( is_array($arg) ? $arg[0]: $arg , FILTER_VALIDATE_FLOAT) ) {
			return  (object) ['valid' => true];
		}else {
			return  (object) ['valid' => false, 'error' => 'Not is a valid Float value!'] ;
		}
	}


	private function str($arg){
		if ( is_string( is_array($arg) ? $arg[0]: $arg ) ) {
			return  (object) ['valid' => true];
		}else {
			return  (object) ['valid' => false, 'error' => 'Not is a valid  String!'] ;
		}
	}
	

	private function string($arg){
		return $this->str(is_array($arg) ? $arg[0]: $arg);
	}


	private function url($arg){
		if (filter_var( is_array($arg) ? $arg[0]: $arg , FILTER_VALIDATE_URL)  ) {
			return  (object) ['valid' => true];
		}else {
			return  (object) ['valid' => false, 'error' => 'Not is a valid  Url!'] ;
		}
	}


	private function email($arg){
		if ( preg_match('/^([a-z0-9._-]{2,}@[0-9a-z_-]{2,}[.a-z]{2,}[.a-z0-9]{2,})$/' ,is_array($arg) ? $arg[0]: $arg) ) {
			return  (object) ['valid' => true];
		}else {
			return  (object) ['valid' => false, 'error' => 'Not is a valid Email address!'] ;
		} 
	}



	private function username($arg){
		if ( preg_match('/^([a-zA-Z0-9_.-@]{0,})$/',is_array($arg) ? $arg[0]: $arg ) ) {
			return  (object) ['valid' => true];
		}else{
			return  (object) ['valid' => false, 'error' => "The username should contain only uppercase and lowercase letters, numbers, and the special characters: dot, underline, dash and at sign.
			Regular Expiration: [a-zA-Z0-9 _.-@]"] ;
		}
	}



	private function exists(array $arg) {
		
		if( $this->class ){
			$obj = $this->class::find($arg[2], $arg[0] );
			$prop = $arg[2];
			if (  ($obj != false) && ($obj->$prop ==  $arg[0] ) ) {
				return  (object) ['valid' => true] ;
			}

		}
					
		return  (object) [
				'valid' => false, 
				'error' => ucfirst($arg[2]).':'.$arg[0] ." no exists in the database !"
			] ;
		
		 
	}

	private function noexists(array $arg) {
	//array(3) { [0]=> value [1]=> class [2]=> key }
		if ( $this->exists($arg)->valid == false ) {
			return  (object) ['valid' => true];
		}else{		
			return (object) [
			'valid' => false,
			 'error' =>  'Value already exists in the database!'
			 ] ;
		} 
	}


	
	private function minlen($arg){
		$value = (int) is_array($arg) ? $arg[0] : $arg ;
		$min = (int) is_array($arg) ? $arg[1] : 1;
	
		if ( $this->str((string) $value)->valid && ( strlen($value) >= $min) ){
			return  (object)  ['valid' => true];
		}else {
			return (object) ['valid' => false , 'error' =>  'The minimum number of characters is '.$min.'!'];
		}	
	}


	private function maxlen($arg){
		$value = (int) is_array($arg) ? $arg[0] : $arg ;
		$max = (int) is_array($arg) ? $arg[1] : 256;
	
		if ( $this->str((string) $value)->valid && ( strlen($value) <= $max) ){
			return  (object)  ['valid' => true];
		}else {
			return (object) ['valid' => false , 'error' =>  'The maximum number of characters is '.$max.'!'];
		}	
	}


	private function mincount($arg){
		$value = (int) is_array($arg) ? $arg[0] : $arg ;
		$min = (int) is_array($arg) ? $arg[1] : 1;
	
		if ( $this->int((int) $value )->valid && ( $value >= $min )  ){
			return  (object)  ['valid' => true];
		}else {
			return (object) ['valid' => false , 'error' =>  'The minimum count must not be less than '.$min.'!'];
		}
	}


	private function maxcount($arg){
		$value = (int) is_array($arg) ? $arg[0] : $arg ;
		$max = (int) is_array($arg) ? $arg[1] : 1000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000;
	
		if ( $this->int((int) $value )->valid && ( $value <= $max )  ){
			return  (object)  ['valid' => true];
		}else {
			return (object) ['valid' => false , 'error' =>  'The maximum value count must not be greater than '.$max.'!'];
		}
	}


	private function boolean($arg){
		$value = (int) is_array($arg) ? $arg[0] : $arg ;
		
		if ( ( (string) $value == true) | ( (string) $value == false) ) {
			return (object) ['valid' => true ];
		}else{
			return (object) ['valid' => false , 'error' =>  'Not is Boolean valid Value!' ];
		}
	}




	private function confirm(array $arg){
		$value = isset($this->data[$arg[1]]) ? $this->data[$arg[1]] : '';	
		if ( ($arg[0] == $value) | $this->compare_hash( [ $arg[0], $value ] )->valid  )  {
			return (object) ['valid' => true ];
		}else{
			return (object) ['valid' => false , 'error' => 'Not is a valid confimation Value!' ];
		} 
	}



	private function compare_hash(array $arg){
		$value = isset($this->data[$arg[1]]) ? $this->data[$arg[1]] : '';		
		if (  password_verify( $arg[0], $value )  |  password_verify( $arg[0], $arg[1] ) ) {
			return (object) ['valid' => true ];
		}else{
			return (object) ['valid' => false , 'error' => 'Not is a valid confimation Value!' ];
		} 
	}




	private function null($arg){
		if ( is_null( is_array($arg) ? $arg[0]: $arg  ) ) {
			return (object) ['valid' => true];
		}else{
			return (object) ['valid' => false, 'error' => 'Not is a Nullable Value!' ];
		} 
	}



	private function nonull($arg){
		if ( !is_null( is_array($arg) ? $arg[0]: $arg ) ) {
			return (object) ['valid' => true];
		}else{
			return (object) ['valid' => false, 'error' => 'This is a Nullable Value!' ];
		} 
	}


	private function startwith($arg){

		if ( substr( strtolower($arg[0]), 0, strlen($arg[1])) ===  strtolower($arg[1]) ) {
			return (object) ['valid' => true];
		}else{
			return (object) ['valid' => false, 'error' => "Value no startwith '$arg[1]' !" ];
		} 
	}


	private function endwith($arg){
		
		if ( substr( strtolower($arg[0]), -abs(strlen($arg[1])) ) === strtolower($arg[1]) ) {
			return (object) ['valid' => true];
		}else{
			return (object) ['valid' => false, 'error' => "Value no startwith '$arg[1]' !" ];
		} 
	}












}




?>