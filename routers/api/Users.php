<?php

use App\Models\User;

//admin painel
$app->get('/admin', function($app,$args) {  
	return $app->controller('Auth@index', $args);
 } );

//login
$app->get('/login', function($app,$args){
	return $app->redirect('/admin' );
}, 'guest');

$app->post('/login', function($app,$args){
	return $app->controller('Auth@login', $args);
});

//logout
$app->get('/logout', function($app,$args){
	return $app->controller('Auth@logout', $args);
}, 'guest,admin,manager');


//list
$app->get('/users', function($app, $args){
	return $app->redirect('/users/1/12');
} , 'admin');

$app->get('/users/{page}int/{ppage}int', function($app, $args){
	return $app->controller('users@list', $args);
} , 'admin');


//create
$app->post('/users', function($app, $args){
	return $app->controller('users@create', $args);
} , 'admin');



//update
$app->post('/users/edit', function($app, $args){
	return $app->controller('users@update', $args);
} , 'auth,admin');




//get profile
$app->get('/users/{id}int', function($app, $args){
	$user =  User::find('id', $args->id);
	unset($user->pass);
	return $app->json( ['user' =>  $user ]);
} , 'auth,admin,guest', 200);



//delete
$app->post('/users/del', function($app, $args){
	return $app->controller('users@delete', $args);
},'admin' );


//Img upload
$app->post('/users/img', function($app, $args){
	return $app->controller('users@img_upload', $args);
},'admin' );