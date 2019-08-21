<?php

$app->get('/teste', function($app,$args) {  
	return  $app->controller('teste@index',  ['name' => 'Messias Dias'] );
 } );


$app->get('/teste/{name}str', function($app,$args) {  
	return  $app->controller('teste@index', (array) $args);
 } );

