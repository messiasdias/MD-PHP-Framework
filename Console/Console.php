<?php

/**
 * Console Class
 */

namespace App\Console;
use App\App;



class Console extends App {

    public $config;
    public static $start = true;

    public function __construct($config=null){
        $this->set_config($config);
        if(self::$start){
            $this->log("#### " . $this->config->description." #### \n" );
            self::$start = false;
        }
    }


    public function set_config($config=null){
        $this->config = (object) [];

        $this->config->path = getcwd().'/';
        $this->config->vendor_path   = $this->config->path.'/vendor/messiasdias/md-php-framework-lib/';
        $this->config->mode = 'console';

        if( file_exists( $this->config->path.'/config/app.php' ) ){
			include $this->config->path.'/config/app.php'; //Load AppConfigs
		}else{
            $this->config->timezone = 'America/Recife';
            $this->config->debug = true;
            $this->config->debug_msg = false;
        }

        $config_array =  ( !is_null($config) && is_array($config) ) ? $config : $this->config;
		foreach($config_array as $key => $value ){
			if($key !== 'debug' ) $this->config->$key = $value;
		}
    }


    public static function log($msg, $class = 0){

        $class_name = '';
        switch($class){
            case 1:
                $class_name = 'Success:';
            break; 
            case 2:
                $class_name = 'Warnnig:';
            break;
            case 3:
                $class_name = 'Error:';
            break;
            case 4:
                $class_name = 'Info:';
            break;
            case 5:
                $class_name = 'Usage:';
            break;
            default:
            break;
        }
        echo $class_name.' '.$msg."\n";
    }



}