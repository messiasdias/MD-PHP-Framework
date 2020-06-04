<?php
namespace App\View;
use App\App;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/**
 * View Class 
 */
class View
{	
	private $view, $path, $app ;
		

	function __construct(App &$app, string $path, string $name, $data=[] )
	{
		$this->app = $app;
		$this->path = $path;		
		
		$template = $this->extractTemplate($name, $this->path, $data);
		$view = new Environment(new FilesystemLoader($this->path));
		$this->setFilters($view);
		$this->setData($data);

		if( file_exists($path.$template) ){
			$this->view = $view->render($template , $data);
	    }else{
		   $this->view = $data['subtitle'];
	    }
	
		return $this;
	}


	private function extractTemplate(string $name, string &$path, &$data=[] ) : string
	{
		$template = false;

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

		if(  isset($template) && !file_exists($path.$template) ){
			$this->app->response->setCode(500);
			$data = ['title' => 'Template Not Found!','subtitle' => "Template File <b>$template</b> no exists in <u>$path</u> !"];
			$path = $this->app->config->views;
			$template = 'layout/msg.html';
		}

		return  $template;
	
	}
	


	private function setFilters(&$view){
		
		if( file_exists(__DIR__.'/Filters.php') ){
			include __DIR__.'/Filters.php'; 
			foreach( $defaults as $key => $filter){
				$view->addFunction( 
					new \Twig\TwigFunction($key, $filter )
				);
			}
		}else{
			echo "File ".__DIR__."/Filters.php not Found!";
		}
	}


	private function setData(array &$data){
		$default_data = [];
		$file = __DIR__.'/Data.php';
		if( file_exists ($file) ) include $file;
		$data = array_merge($default_data, $this->app->response->getData());
		$data = array_merge($data, [ 'View' => json_encode($data) ] ) ;
		$this->data = $data;
	}

	
	public function show(){
		return $this->view; 
	}


}