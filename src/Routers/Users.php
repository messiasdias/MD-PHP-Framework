<?php
use App\Models\User;


//list

$app->router_group(['/users', '/users/list', '/users/{page}int/{ppage}int'], function($app, $args){
	return $app->controller('users@list', $args);
} , 'admin');


//search
$app->post('/users/search', function($app, $args){
	return $app->controller('users@search', $args);
} , 'admin', 200);



//create form
$app->get('/users/add', function($app, $args){
	$args->type = 'add';
	return $app->view('adminlte/users/form', ['type' => $args->type ]);
} , 'admin');


//create
$app->post('/users/add', function($app, $args){
	return $app->controller('users@create', $args);
} , 'admin');




//update update form - {attr} => name|email|type|pass
$app->get('/users/edit/{attr}str|minlen:4/{id}int|mincount:1', function($app, $args){

	if($args->attr == 'type' && ($app->user()->rol != 1) ){
		return $app->response->set_http_code(401);
	}
	
	$app->inputs(User::find('id', $args->id));
	return $app->view('adminlte/users/form', ['type' => $args->attr ]);
} ,  'manager,admin');


//update update form - {attr} => name|email|type|pass
$app->get('/users/edit/{id}int|mincount:1', function($app, $args){
	$args->attr = 'edit';
	var_dump($app->request->data['id']); exit;
	if( ($args->attr == 'type' && ($app->user()->rol != 1)) | ($args->attr == 'status' && ($app->user()->id == $app->request->data['id'])) ){
		return $app->response->set_http_code(401);
	}
	return $app->controller('users@update_form', $args );
} ,  'admin');


//update
$app->post('/users/edit', function($app, $args){
	return $app->controller('users@update', $args);
} , 'auth');




//get profile
$app->router_group(['/users/profile/{id}int', '/users/{id}int' ], function($app, $args){
	return $app->view('adminlte/users/profile', ['user_item' =>  User::find('id', $args->id) ]);
} , 'auth');


//delete
$app->post('/users/del', function($app, $args){
	return $app->controller('users@delete', $args);
},'admin' );


//Img upload
$app->post('/users/img', function($app, $args){
	return $app->controller('users@img_upload', $args);
},'admin, manager' );