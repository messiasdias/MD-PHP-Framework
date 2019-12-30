<?php

//Maker Routers
use App\Maker\Maker;



$app->get('/maker',  function($app,$args) {  
	//Maker index|help	
	return $app->view('help' , [ 'commands' => maker_commands ] , $app->vendor_path .'Maker/views'  ) ;
 }, 'debug');



$app->get('/maker/{command}string/{subcommand}string', function($app,$args) {  
	//Maker File | Migrate	

	$maker = new Maker($app);
	$command = $args->command; $continue = false;

	foreach (maker_commands as $i => $maker_command) {
		if($maker_command['name'] == $command){
			$continue = true;
		}
	}

	if($continue && ( strtolower($args->subcommand) != 'help')  ){
		return $app->write($maker->$command($args->subcommand) , 200) ;
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
			return $app->view('map' , [ 'routers' => $app->routers] , $app->vendor_path .'Maker/views/' ) ;
		break;
		
		case 'api' : 
		case 'json' : 
			return $app->json(  [ 'routers' => $app->routers] );
		break;
	}

 }, 'debug');
