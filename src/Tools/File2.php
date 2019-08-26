<?php
namespace App\Tools;
use App\App;
/**
 * File2 Class
 */
class File2
{
	public $path, $name, $type, $tmp_name, $size, $error;	
	
	function __construct($file=null)
	{
        $this->load_attr($file);
    }

    private function load_attr($file=null){
        if ( !is_null($file) ) {

            if ( is_array($file) ){
                
                foreach ($file as $key => $value) {
                    if ( array_key_exists( $key, $file) && property_exists(get_called_class() , $key) ){
                        $this->$key = $file[$key];
                    }	
                }

            }	
            elseif( is_string($file) && App::validate($file, 'string|min:4') ){

                if( (strpos ( $file, '/' ) === 0 ) | ( strpos ( $file, '/' ) > 1) ){   
                    $this->name = pathinfo($file)['basename'] ;
                    $this->path = pathinfo($file)['dirname'].'/';
                }

                $this->name = $file;
            }

            $this->path =  !is_null($this->path) ? $this->path : $this->get_default_path();

            return true;

		}else{
            return false;
        }
    }


    private function get_default_path(){

        switch( $this->type ){

            case 'image/gif':
            case 'image/jpeg':
            case 'image/jpg':
            case 'image/png':
            case 'image/ico':
            case 'image/vnd.microsoft.icon':
                return "../assets/public/img/";
            break;

            case 'application/pdf':
                return "../assets/public/pdf/";
            break;

            default:
                return "../assets/public/outhers/";
            break;

        }


    }  


    public function upload(){
        $upload_file = $this->path.$this->name;    

        if( !file_exists($upload_file) ){
            if( move_uploaded_file($this->tmp_name, $upload_file)  ) {
                return (object) [ 'status' => true, 'msg' => "File '$upload_file' uploaded Successfully!" ] ;
            }
        }else{
            return (object) [ 'status' => false, 'msg' => "File ' $upload_file' already exists!" ] ;
        }

        return (object) [ 'status' => false , 'msg' => "Error while uploading the File ' $upload_file'!" ] ;;
    }


    public function download(){

        $download_file = $this->path.$this->name;        
          
        if( file_exists( $download_file ) ){
            header('Content-Type: " application/'.pathinfo($download_file)['extension'].'"');
            header('Content-disposition: attachment; filename="'.$this->name.'" ');
            header('Pragma: no-cache');

            if(  readfile($download_file) ){
                return (object) ['status'=> true, 'msg' => "Download file $this->name Successfully Completed!"] ;
            }

        }

        return (object) ['status'=> false, 'msg' => "File $this->name not found!"]; 

    }

     




}