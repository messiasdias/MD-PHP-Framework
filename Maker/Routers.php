<?php

//Maker Routers
use App\Maker\Maker;


$app->get('/maker',  function($app,$args) {  
	//Maker index|help
	$maker = new Maker($app);	
	return $app->view('help' , [ 'commands' => $maker->commands() ] , $app->config->vendor_path .'Maker/views'  ) ;
 }, 'debug');


$app->router_group(['/maker/{command}string/{subcommand}string', '/maker/{command}string' ], function($app,$args) {  
	//Maker File | Migrate	
	$maker = new Maker($app);
	$continue = false;

	foreach ($maker->commands() as $i => $command ) {
		if($command->name == $args->command){
			$continue = true;
		}
	}
	
	$method = $args->command;

	if($continue && ( isset($args->subcommand) && ( strtolower($args->subcommand) != 'help'))  ){
		return $app->write($maker->$method($args->subcommand) , 200) ;
	}else{
		return $app->redirect('/maker');
	}


 }, 'debug' );


 $app->router_group(['/map', '/map/{mode}string'], function($app,$args) {  
	//Router Map	
	if( isset($args->mode ) ){
		$app->mode = $args->mode;
	}else{
		$args->mode = 'app';
	}

	switch( $args->mode ){
		default:
		case 'view' : 
		case 'app' :
		case 'html' :  
			return $app->view('map' , [ 'routers' => $app->routers] , $app->config->vendor_path .'Maker/views/' ) ;
		break;
		
		case 'api' : 
		case 'json' : 
			return $app->json(  [ 'routers' => $app->routers] );
		break;
	}

 }, 'debug');
