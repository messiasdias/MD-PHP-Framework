<?php


$app->get('/galery/{galery}string|minlen:1', function($app, $args){
    $data=[]; $jobs_array=[]; $demos_array=[];
    $jobs = $app->db('Job')->select(['publish','1']) ; 
    $demos = $app->db('Demo')->select(['publish', '1'])  ;

    if( is_array( $demos ) ){
        $demos_array =  $demos;
    }else{
        $demos_array[0] = $demos; 
    }

    if( is_array( $jobs ) ){
        $jobs_array = $jobs;
    }else{
        $jobs_array[0] = $jobs; 
    }

    if( strtolower($args->galery) == 'all'  ){
        $data = array_merge( $jobs_array , $demos_array );
    }elseif( (strtolower($args->galery) == 'jobs' ) | strtolower($args->galery) == 'demos'  ) {
        $array = strtolower($args->galery).'_array';
        $data = (array) $$array;
    }else{
        return $app->json(['itens' => false, 'status' => ['code'=> 404 , 'msg' => 'Galery '.$args->galery.' inexistent or items not found! ' ]  ] );
    }

    return $app->json( ['itens' => $data ] );
}); 



//Search
$app->post('/galery/search/{galery}string|minlen:3', function($app, $args){

    $data=[];
    $itens_jobs = @$app->controller('galery@search2', (object) ['class' => 'Job' ]);
    $itens_demos = @$app->controller('galery@search2', (object) ['class' => 'Demo' ]);
 
    switch (strtolower($args->galery) ) {
        case 'jobs':
             $data['itens'] =  $itens_jobs;
        break;

        case 'demos':
            $data['itens'] =  $itens_demos  ;
        break;

        default:
        $data['itens'] = array_merge($itens_jobs, $itens_demos);
        break;
    }


    return $app->json($data);
});





$app->router_group([
    
    [ 'url' => '/galery/teste/1', 'method' => 'get' ] ,
    [ 'url' => '/galery/teste/3' ],
    '/home',
    '/galery/teste/2',
    '/galery/testando' 

] , function($app, $args){
     return $app->view('home');
}); 


