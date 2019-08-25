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

//search
$app->post('/users/search', function($app, $args){
	return $app->controller('users@search', $args);
} , 'admin', 200);




//create form
$app->get('/users/add', function($app, $args){
	return $app->controller('users@create_form', $args);
} , 'admin');


//create
$app->post('/users', function($app, $args){
	return $app->controller('users@create', $args);
} , 'admin');




//update update form - {attr} => name|email|type|pass
$app->get('/users/edit/{attr}str|minlen:4/{id}int|mincount:1', function($app, $args){
	$args->type = strtolower($args->attr);
	$args->redirect = "/users";
	return $app->controller('users@update_form', $args );
} ,  'auth,admin');


//update
$app->post('/users/edit', function($app, $args){
	return $app->controller('users@update', $args);
} , 'auth,admin');




//get profile
$app->get('/users/profile/{id}int', function($app, $args){
	return $app->view('users/profile', ['user_item' =>  User::find('id', $args->id) ]);
} , 'auth,admin');

$app->get('/users/{id}int', function($app, $args){
	return $app->redirect('/users/profile/'.$args->id);
} , 'auth,admin', 200);



//delete
$app->post('/users/del', function($app, $args){
	return $app->controller('users@delete', $args);
},'admin' );


