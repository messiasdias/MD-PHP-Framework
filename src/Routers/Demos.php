<?php
use App\Models\Demo;
use App\Models\Job;



//Single
$app->get('/galery/{galery}string|minlen:3/{id}int|mincount:1', function($app, $args){
    $data=null;
    if( isset($args->galery) && isset($args->id )) {
		$class = "App\Models\\" ;
		switch( strtolower($args->galery) ){
			case 'jobs':
				$class .= "Job";
			break;

			case 'demos':
				$class .= "Demo";
			break;
		}

        return $app->view('visualize', ['galery_single' => $class::find('id', $args->id )]); 
    }
	 return $app->redirect('/');
});



//List
$app->get('/demos', function($app, $args){
	return $app->redirect('/demos/1/10');
});

$app->get('/demos/{page}int|mincount:1/{ppage}int|mincount:1', function($app, $args){
	$args->class = 'Demo';
	return $app->controller('galery@list_all', $args );
});


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


//Create name form
$app->get('/demos/add', function($app, $args){
	return $app->view('adminlte/galery/form', ['type' => 'add' , 'class' => 'Demo' ]);
} , 'manager,admin');


//Update
$app->get('/demos/edit/{id}int|mincount:1', function($app, $args){
	$app->inputs(Demo::find('id', $args->id) );
	return $app->view('adminlte/galery/form', ['type' => 'edit' , 'class' => 'Demo']);
} , 'manager,admin');

//Update form
$app->post('/demos/edit', function($app, $args){
	$args->class = "Demo";
	return $app->controller('galery@save', $args );
} , 'manager,admin');

//Update publish
$app->post('/demos/publish', function($app, $args){
	$args->class = "Demo";
	return $app->controller('galery@publish', $args );
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