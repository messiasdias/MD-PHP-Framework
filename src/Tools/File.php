<?php
namespace App\Tools;
use App\App;
/**
 * File Class
 */
class File
{
	private $name, $new_name, $type, $tmp_name, $size, $max_size, $min_size, $error;	
	
	function __construct($file, array $args=null)
	{

		if ( is_array($file) && !is_null($file)){
			
			foreach ($file as $key => $value) {

				if ( array_key_exists( $key, $file) && property_exists(get_called_class() , $key) ){

					$this->$key = $file[$key];
				}	

			}

		}	
		elseif( is_string($file) && App::regex($file, 'string|min:5') ){
			$this->name = $file;
		}else{
			return false;
		}


		if ( !is_null($args) ) {
			foreach ($args as $key => $value) {
				if ( array_key_exists( $key, $args) && property_exists(get_called_class() , $key)  ){
					$this->$key = $args[$key];
				}
			}
		}

		$this->max_size = is_null($this->max_size) ? $this->size: $this->max_size;
		$this->min_size = is_null($this->min_size) ? $this->size: $this->min_size;

	}

	public function upload(){

		if ( !file_exists($this->name)  && ( $this->test_size('<', $this->max_size)  && $this->test_size('>', $this->min_size) ) ){
			return move_uploaded_file($this->tmp_name, $this->name );
		}

		return false;
	}

	public function download(){
		header('Content-Type: "'.$this->type.'"');
		header('Content-disposition: attachment; filename="'.$this->name.'" ');
		header('Pragma: no-cache');
		readfile($this->name);
		return;
	}

	public function delete(){
	
		if (file_exists($this->name)){
			return unlink($this->name);
		}
	}


	public function move(string $new_name=null){
		if ( file_exists($this->name) && !is_null($new_name) ){
			return move_uploaded_file($this->name, $new_name );
		}
		return false;
	}

	private function test_size(string $arg=null, $value=null){
		$value = (int) $value;

		switch ($arg) {
			case '>':
			 return ( $this->size >= $value ) ? true : false;
				break;
			case '<':
				 return ( $this->size <= $value ) ? true : false;
				break;
				
			case '==':
				 return ( $this->size == $value ) ? true : false;
			break;	

			case 'null':
				 return ( is_null($this->size) ) ? true : false;
			break;	
			
			default:
				return false;
			break;
		}

	}

}