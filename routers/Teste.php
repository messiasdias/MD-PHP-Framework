<?php

$app->get('/teste', function($app,$args) {  
	return  $app->controller('teste@index',  ['name' => 'Messias Dias'] );
 } );


$app->get('/teste/{name}str', function($app,$args) {  
	return  $app->controller('teste@index', (array) $args);
 } );



 $app->get('/update', function($app,$args) {  
	return  $app->view('teste/update', ['title' => 'teste update']);
 } );

 $app->post('/update', function($app,$args) {  
	//return  $app->controller('teste@index',  ['name' => 'Messias Dias'] );
	echo "Uploaded File: <br>";
	var_dump($_FILES ); exit;

 } );