<?php

namespace App\ORM;
use App\App;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

/**
 *   DB Class
 */

class DB {

    private $orm , $db;

    public function __construct($entityPath = null){
        
        if(is_null($entityPath)) $entityPath = getcwd()."/../src/Models/";

        App::setEnv();
        $this->orm = Setup::createAnnotationMetadataConfiguration([$entityPath, getcwd()."/../src/"], (App::getEnv()->app_env == "dev") , null,null,false);
        
        $this->db = array(
                'driver' => App::getEnv()->db_driver ?? "pdo_mysql",
                'host' => App::getEnv()->db_host ?? 'localhost',
                'port' => App::getEnv()->db_port ?? 3306,
                'user'     => App::getEnv()->db_user ?? "root",
                'password' => App::getEnv()->db_pass ?? "" ,
                'dbname'   => App::getEnv()->db_name,
        );         
    }


    public function getManager() {
        return EntityManager::create( $this->db, $this->orm);
    }


}
