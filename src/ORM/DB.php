<?php

namespace App\ORM;
use \Doctrine\ORM\Tools\Setup;
use \Doctrine\ORM\EntityManager;

/**
 *   DB Class
 */

class DB {

    private $config = [], $host, $port, $database, $user, $pass, $driver, $path;

    public function __construct($entityPath = null, $configFile = null, $isDevMode = false){
        
        if( is_null($configFile) ){
            $configFile = getcwd()."/../config/db.php";
        }

        if( is_null($entityPath) ){
            $entityPath = getcwd()."/../src/Models/";
        }
        
        $this->config['orm'] = Setup::createAnnotationMetadataConfiguration(array($entityPath ?? __DIR__."/src"),$isDevMode, null,null, false);

        if( !is_null( $configFile ) && file_exists($configFile)  ){
            //Load DBConfigs
            include  $configFile;  

            $this->config['db'] = array(
                'driver' => $this->driver ?? "pdo_mysql",
                'host' => $this->host ?? 'localhost',
                'port' => $this->port ?? 3306,
                'user'     => $this->user ?? "root",
                'password' => $this->pass ?? "" ,
                'dbname'   => $this->database,
            );

            if( strtolower($this->config['db']['driver']) == 'sqlite' ){
                $this->config['db']['path'] = $this->path;
            }

        }else{
            echo "File config/db.php not Found!";
            exit; 
        }         
    }


    public function getManager() {
        return EntityManager::create( $this->config['db'],  $this->config['orm']);
    }


}
