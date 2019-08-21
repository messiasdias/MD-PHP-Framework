<?php
namespace App\Tools;
/**
 * View Class 
 */
class View
{	
	private $view;
		

	function __construct(string $name,$data=null,string $path=null)
	{
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


		$view = new \Twig_Environment(new \Twig_Loader_Filesystem($path));
		$this->view = $view->render($template , $data);

		return $this;
	}

	public function show(){
		return $this->view; 
	}


}