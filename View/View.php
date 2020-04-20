<?php
namespace App\View;
use App\App;

/**
 * View Class 
 */
class View
{	
	private $view, $app;
		

	function __construct(App &$app, string $path, string $name, $data=[] )
	{
		$this->app = $app;	
		$template = $this->extractTemplate($name, $path, $data);
		$view = new  \Twig\Environment(new \Twig\Loader\FilesystemLoader($path));
		$this->set_filters($this->app, $view);

		if( file_exists($path.$template) ){
			$this->view = $view->render($template , $data);
	    }else{
		   $this->view = $data['subtitle'];
	    }
	
		return $this;
	}


	private function extractTemplate(string $name, string &$path, &$data=[] ) : string
	{

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

			$content = glob( $path.$explode[0].'.*' ); 
			if( count($content) >= 1 ){
				$template = str_replace($path,'',$content[0]);
			}
			unset($content);

			$content = glob( $path.$explode[1].'.*' ); 
			if( count($content) >= 1 ){
				$data['content'] = str_replace($path,'',$content[0]);
			}

			if ( count($explode) > 2  ){
				 $i = 2;
				 while ( $i <= count($explode) ) {
					$content = glob( $path.$explode[$i].'.*' ); 
					if( count($content) >= 1 ){ 
					 $data['content'.$i] = str_replace($path,'',$content[0]);
					}
					 $i++;
				 }
			}

		}else{
			$content = glob( $path.$name.'.*' ); 
			if( count($content) >= 1 ){ 
				$template = strtolower(str_replace($path,'',$content[0]));
			}
		}

		if( !file_exists($path.$template) ){
			$this->app->response->setCode(500);
			$data = ['title' =>'Template Not Found!','subtitle' => "Template File <b>$template</b> no exists in <u>$path</u> !"];
			$path = $this->app->config->views;
			$template = 'layout/msg.html';
		}

		return  $template;
	}
	

	private function set_filters(App $app, &$view ){

		$view->addFunction( 
			new \Twig\TwigFunction('middlewares', function (string $list, $obj=null, $denyAcess=false) {
				$this->app->middlewares($list, $obj ?? $this->app->middleware_obj, $denyAcess);
				return $this->app->middleware_auth;
			})
		);
		
		if( file_exists($this->app->config->vendor_path.'View/Filters.php') ){
			include $this->app->config->vendor_path.'View/Filters.php'; //Load Custom Filters Functions
		}else{
			echo "File ".$this->app->config->vendor_path."View/Filters.php not Found!";
		}

	}


	public function show(){
		return $this->view; 
	}


}