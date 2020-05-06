<?php

/**
 *  Model Class
 */
namespace App\ORM;
use \Doctrine\ORM\Tools\Setup;
use \Doctrine\ORM\EntityManager;

class ORM{

    private $config = [], $host, $port, $database, $user, $pass, $driver, $path;

    public function __construct($entityPath = null, $configFile = null, $isDevMode = false){
        
        $this->config['orm'] = Setup::createAnnotationMetadataConfiguration(array($entityPath ?? __DIR__."/src"),$isDevMode);

        if( !is_null( $configFile ) && file_exists($configFile)  ){
            //Load DBConfigs
            include  $configFile;  

            $this->config['db'] = array(
                'driver' => $this->driver ?? 'mysql',
                'host' => $this->host ?? 'localhost',
                'port' => $this->port ?? 3306,
                'user'     => $this->user,
                'password' => $this->pass ,
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
