<?php

$app->get('/teste', function($app,$args) {  
	return  $app->controller('teste@index',  ['name' => 'Messias Dias'] );
 } );


$app->get('/teste/{name}str', function($app,$args) {  
	return  $app->controller('teste@index', (array) $args);
 } );



 $app->get('/upload', function($app,$args) {  
	return  $app->view('teste/upload', ['title' => 'teste update']);
 } );

 $app->post('/upload', function($app,$args) {  
	return  $app->controller('teste@upload', (array) $args);
 } );


 $app->get('/download', function($app,$args) {  
	return  $app->view('teste/download');
 } );


 $app->post('/download', function($app,$args) {  
	return  $app->controller('teste@download',  $args);
 } );