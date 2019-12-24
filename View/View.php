<?php
namespace App\View;
use App\App;
/**
 * View Class 
 */
class View
{	
	private $view, $app;
		

	function __construct(App $app, string $name,$data=null,string $path=null)
	{
		$this->app = $app;	
		$template = '';
		if ( strpos($name, '.') |   strpos($name, ':') | strpos($name, ',') ) {
			
				$delimiter=null;
				if ( strpos($name, '.') ){
					$delimiter = ".";
				}elseif ( strpos($name, ':') ){
					$delimiter = ":";
				}
				elseif ( strpos($name, ',') ){
					$delimiter = ",";
				}
				elseif ( strpos($name, '|') ){
					$delimiter = "|";
				}
				elseif ( strpos($name, ';') ){
					$delimiter = ";";
				}

				$explode = explode($delimiter, strtolower($name));
				
				$template = $explode[0];
				$data['content'] = $explode[1].'.html';

				 if ( count($explode) > 2  ){
			 		$i = 2;
			 		while ( $i <= count($explode) ) {
			 			$data['content'.$i] = $explode[$i].'.html';
			 			$i++;
			 		}
				 }

		}else{
			$template = strtolower($name);
		}


		if ( !strrpos('.html', $template)  ){
			$template = strtolower($template).'.html';
		}

		if( is_null($path) ) {
			$path = views_dir; //Var defined on config file
		}
		
		$path = $app->path.$path;
		$view = new  \Twig\Twig_Environment(new  \Twig\Twig_Loader_Filesystem($path));
		$view = $this->set_filters($this->app, $view);
		$this->view = $view->render($template , $data);

		return $this;
	}

	

	private function set_filters(App $app, $view ){

		$view->addFunction( 
			new \Twig\TwigFunction('middleware', function (string $list) {
				$this->app->middlewares($list);
				return $this->app->middleware_auth;
			})
		);
		
		include 'View_Filters.php'; //Load Custom Filters Functions
		return $view;
	}


	public function show(){
		return $this->view; 
	}


}