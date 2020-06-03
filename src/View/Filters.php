<?php
/* ViewFiltes 
    ex: 
    
	//array
    $defaults = [
        //optional '$arg' and 'use (&$app)'
        'function_name' => function ($arg) use (&$app) {
            echo $text;
        },
    ];

*/

$defaults = [

    'middlewares' => function (string $list, $obj=null, $denyAcess=false) {
        $this->app->middlewares($list, $obj ?? $this->app->middleware_obj, $denyAcess);
        return $this->app->middleware_auth;
    },

    'isString' => function($str) {
        return is_string($str);
    },
    
    'isArray' => function($arr) {
        return is_array($arr);
    },

    'isObject' => function($obj) {
        return is_object($obj);
    },
    
    'convert_object' => function($item){
      return is_array($item) ? (object) $item : $item ;
    },
    
    'convert_array' => function($item){
        return is_object($item) ? (array) $item : $item ;
    },
];




foreach ( glob( $path.'../filters/*.php' )  as $filterfile ) {
    include_once $filterfile ; 
    $defaults = array_merge($defaults, $filters);
}
