<?php

/**
 *  Model Class
 */
namespace App\ORM;



/**
 * @Entity @Table(name="")
 */

class Model extends EntityManager {

    protected $entityManager;


    public function __construct(){

    }

    public function getManager() : EntityManager {
        
    }


}