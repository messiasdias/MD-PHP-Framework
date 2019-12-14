<?php
use App\App;

/* Middlewares 

    ex: 
   
 $this->middlewares = (object) [   

    'name' => function(App $app){
        return true;
    },

    ...
 ];

*/

$this->middlewares = (object) [

    //guest
    'guest' => function(App $app){
        return !$app->user() ? true : false;
    },

    //admin
    'admin' => function(App $app){
        return ( $app->user() && ($app->user()->rol == 1 ) ) ;
    },


    //manager
    'manager' => function(App $app){
        return ( $app->user() && ($app->user()->rol == 2) ) ;
    },

    //auth
    'auth' => function(App $app){
        return $app->user() ? true : false;
    },

    //Author
    'author' => function (App $app){
        return true;
    },


];

