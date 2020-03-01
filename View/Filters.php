
<?php

/*

    Example:

    $view->addFunction(
        new \Twig\TwigFunction('function_name', function ($arg) {
            return $arg;
        })
    ); 


*/


function convert_object($item){
  return is_array($item) ? (object) $item : $item ;
}

function convert_array($item){
    return is_object($item) ? (array) $item : $item ;
}


foreach ( glob( $this->app->config->path.'src/Viewfilters/*.php' )  as $filter ) {
    if (  file_exists($filter) )
    {	
        include $filter;
    }
}


