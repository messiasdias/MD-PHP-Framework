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
        return !$app->user();
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

        if(isset($app->request->data['id']) ){
            return ($app->request->data['id'] == $app->user()->id );
        }
        elseif( isset($app->args->id) ){
            return ( $app->user() && ($app->args->id == $app->user()->id) );
        }else{
            return false;
        }
 
    },


];

