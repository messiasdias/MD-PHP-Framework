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
            $this->log("#### Maker | " . $this->config->description." ####" );
            $this->log("by: Messias Dias | github.com/messiasdias \n" );
            self::$start = false;
        }
    }


    public function set_config($config=null)
    {
        $this->config = (object) [];
        $this->config->path = getcwd().'/';
        $this->config->vendor_path   =  __DIR__.'/../';
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


    public static function log($msg, $class = null)
    {
        /*
            Color refs: 
            https://stackoverflow.com/questions/34034730/how-to-enable-color-for-php-cli/34034922
        */
        $class_name = '';
        $color = "";

        switch($class){
            case 1:
            case 'success':
                //$class_name = 'Success: ';
                $color = "92";
            break; 
            case 2:
            case 'warning':    
                //$class_name = 'Warnnig: ';
                $color = "33";
            break;

            case 3:
            case 'error':    
                //$class_name = 'Error: ';
                $color = "91";
            break;

            case 4:
            case 'info':
                //$class_name = 'Info: ';
                $color = "94";
            break;

            case 5:
            case 'help':
            case 'usage':    
                $class_name = 'Usage: ';
                $color = "96";
            break;

            default:
                $color = "97";
            break;
        }

        echo "\e[".$color."m".$class_name.$msg."\n";
    }



}