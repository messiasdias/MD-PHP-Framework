<?php
/** 
 * Template View includes variables
 * $this --> $app
 */

$default_data = [
    //App description in .env* --> APP_DESCRIPTION
    'description' => $this->app->getEnv()->app_description,

    //Http status
    'http'  => [ 
        'code' => $this->app->response->getCode(),
        'msg' =>  $this->app->response->getMsg(),
        'url'  => $this->app->request->url,
        'referer'  => $this->app->request->referer,
        'host'  => $this->app->request->host,
        'scheme'  => $this->app->request->scheme,
    ],

    //user and access_token if this is authenticated
    'user' => $this->app->user($this->app->request->access_token),
    'access_token' => ($this->app->request->access_token) ? $this->app->request->access_token : false,
 
    //log msg
    'log' => isset($this->app->response) ? (array) $this->app->response->getLog() : false,

    //form inputs validations values
    'inputs' => ($this->app->inputs()) ? $this->app->inputs() : false ,
    //form inputs validations errors
    'errors' => isset( $this->app->response->getLog()->errors) ? (array) $this->app->response->getLog()->errors : false,
    
    //requested sessions
    'session' =>  ($_SESSION) ? ( (object) $_SESSION ) : false,
    //requested cookies
    'cookies' =>  ($this->app->request->cookies) ? ( (object) $this->app->request->cookies) : false,   
]; 