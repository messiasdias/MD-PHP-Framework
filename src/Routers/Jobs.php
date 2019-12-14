<?php
use App\Models\Job;



//List
$app->get('/jobs', function($app, $args){
	return $app->redirect('/jobs/1/10');
});

$app->get('/jobs/{page}int|mincount:1/{ppage}int|mincount:1', function($app, $args){
	$args->class = 'Job';
	return $app->controller('galery@list_all', $args );
});


//List-author
$app->get('/jobs/list', function($app, $args){
	return $app->redirect('/jobs/list/1/10');
}, 'admin, manager');

$app->get('/jobs/list/{page}int|mincount:1/{ppage}int|mincount:1', function($app, $args){
	$args->class = 'Job';
	return $app->controller('galery@list_author', $args );
}, 'admin, manager');



//search
$app->post('/jobs/search', function($app, $args){
	$args->class = "Job";
	return $app->controller('galery@search', $args);
} , 'admin, manager, guest', 200);

//search-author
$app->post('/jobs/list/search', function($app, $args){
	$args->class = "Job";
	return $app->controller('galery@search_author', $args);
} , 'admin, manager', 200);




//Create
$app->post('/jobs/add', function($app, $args){
	$args->class = "Job";
	return $app->controller('galery@save', $args );
} , 'manager,admin');


//Create form
$app->get('/jobs/add', function($app, $args){
	return $app->view('adminlte/galery/form', ['type' => 'add' , 'class' => 'Job' ]);
} , 'manager,admin');


//Update
$app->get('/jobs/edit/{id}int|mincount:1', function($app, $args){
	$app->inputs(Job::find('id', $args->id) );
	return $app->view('adminlte/galery/form', ['type' => 'edit' , 'class' => 'Job' ]);
} , 'manager,admin');

//Update form
$app->post('/jobs/edit', function($app, $args){
	$args->class = "Job";
	return $app->controller('galery@save', $args );
} , 'manager,admin');


//Delete
$app->post('/jobs/del', function($app, $args){
	$args->class = "Job";
	return $app->controller('galery@delete', $args );
} , 'manager,admin');


//Img upload
$app->post('/jobs/img', function($app, $args){
	$args->class = "Job";
	return $app->controller('galery@img_upload', $args);
},'admin, manager' );