<?php

//Maker Routers
use App\Maker\Maker;



$app->get('/maker',  function($app,$args) {  
	//Maker index|help	
	return $app->view('help' , [ 'commands' => maker_commands ] , $app->path.'App/Maker/') ;
 });



$app->get('/maker/{command}string/{subcommand}string', function($app,$args) {  
	//Maker File | Migrate	

	$maker = new Maker($app->path);
	$command = $args->command; $continue = false;

	foreach (maker_commands as $i => $maker_command) {
		if($maker_command['name'] == $command){
			$continue = true;
		}
	}

	if($continue){
		return $app->write( $maker->$command($args->subcommand) , 200) ;
	}else{
		return $app->redirect('/maker');
	}


 } );


 $app->get('/map', function($app,$args) {  
	//Router Map	
	return $this->mode_trigger( 
		function ($app, $args) {
			return $app->view('map' , [ 'routers' => $app->routers] , $app->path.'/Core/Maker/') ;
		},function($app, $args){
			return $this->json(  [ 'routers' => $app->routers] );
		});


 });
