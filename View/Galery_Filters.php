
<?php

//is_publish
$view->addFunction( 
    new \Twig\TwigFunction('is_publish', function ($item) {
        $item = (Object) $item;
        return (is_array($item) &&  (count($item) == 1) ) ? $item[0]->is_publish() : $item->is_publish() ;
    })
);


//is_author
$view->addFunction( 
    new \Twig\TwigFunction('is_author', function ($item) {
       $item = (Object) $item;
        if( $this->app->user() ) { 
            return ( @$item->author_id == $this->app->user()->id ) ? true : false;
        }
        return false;
    })
);