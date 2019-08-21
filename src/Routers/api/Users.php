<?php
use App\Models\User;
use App\Auth\Auth;

//API Routers
$app->get('/teste', function($app, $args){	

	return $app->json( [
		'teste' => 'OK',
		'errors' =>  0
	] , 200);

} ,);

