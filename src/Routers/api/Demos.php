<?php
use App\Models\Demo;

//List-author
$app->get('/demos/list', function($app, $args){
	return $app->redirect('/demos/list/1/10');
}, 'admin, manager');

$app->get('/demos/list/{page}int|mincount:1/{ppage}int|mincount:1', function($app, $args){
	$args->class = 'Demo';
	return $app->controller('galery@list_author', $args );
}, 'admin, manager');



//search
$app->post('/demos/search', function($app, $args){
	$args->class = "Demo";
	return $app->controller('galery@search', $args);
} , 'admin, manager, guest', 200);

//search-author
$app->post('/demos/list/search', function($app, $args){
	$args->class = "Demo";
	return $app->controller('galery@search_author', $args);
} , 'admin, manager', 200);



//Create
$app->post('/demos/add', function($app, $args){
	$args->class = "Demo";
	return $app->controller('galery@save', $args );
} , 'manager,admin');



//Update
$app->post('/demos/edit', function($app, $args){
	$args->class = "Demo";
	return $app->controller('galery@save', $args );
} , 'manager,admin');


//Delete
$app->post('/demos/del', function($app, $args){
	$args->class = "Demo";
	return $app->controller('galery@delete', $args );
} , 'manager,admin');


//Img upload
$app->post('/demos/img', function($app, $args){
	$args->class = "Demo";
	return $app->controller('galery@img_upload', $args);
},'admin, manager' );