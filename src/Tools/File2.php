<?php
namespace App\Tools;
use App\App;
/**
 * File2 Class
 */
class File2
{
	private $path, $name, $new_name, $type, $tmp_name, $size, $max_size, $min_size, $error;	
	
	function __construct($file, array $args=null)
	{

        if ( !is_null($file) ) {

            if ( is_array($file) ){
                
                foreach ($file as $key => $value) {
                    if ( array_key_exists( $key, $file) && property_exists(get_called_class() , $key) ){
                        $this->$key = $file[$key];
                    }	
                }

            }	
            elseif( is_string($file) && App::validate($file, 'string|min:5') ){
                $this->name = $file;
                var_dump( App::validate($file, 'string|min:5') ); exit;
            }

		}else{
			return false;
        } 
        
    }



}