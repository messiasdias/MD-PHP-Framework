<?php

//Maker Routers
use App\Maker\Maker;



$app->get('/maker',  function($app,$args) {  
	//Maker index|help	
	return $app->view('help' , [ 'commands' => maker_commands ] , $app->path.'Maker/') ;
 });


$app->get('/maker/migrate/{command}string', function($app,$args) {  
	//Maker Migrate	
	$maker = new Maker($app->path);
	return $app->write($maker->migrate($args->command), 200 ) ;

 } );


$app->get('/maker/file/{command}string', function($app,$args) {  
	//Maker File	
	$maker = new Maker($app->path);
	return $app->write( $maker->file($args->command) , 200) ;
 } );


 $app->get('/map', function($app,$args) {  
	//Router Map	
	return $app->view('map' , [ 'routers' => $app->routers] , $app->path.'Maker/') ;
 });
