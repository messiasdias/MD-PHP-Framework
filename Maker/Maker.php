<?php
/**
 * Maker Class
 */

namespace App\Maker;
use App\App;
use App\Database\DB;
use App\Database\Table;



class Maker
{
	private $app, $path, $response, $migrations=[], $seeds=[], $tables=null;
	private $spoon_flag, $seeder_objects;

	public function __construct(App $app) {
		$this->app = $app;
		$this->path = $this->app->config->vendor_path.'Maker/';
		if( file_exists($this->app->config->path.'/config/maker.php' ) ){
			include $this->app->config->path.'/config/maker.php';
			$this->args = isset($this->app->maker_config) ? $this->app->maker_config : false;
		}else{
			$this->args = false;
		}
	}
	
	public function commands(){
		return json_decode( file_get_contents($this->path.'commands.json') );
	}


	private function get_classes(string $type,string $class_name=null){

		$path =''; $return=[]; $action ='';
		$class_name = is_null($class_name) ? 'all' : $class_name;

		switch (strtolower($type) ) {
			case 'seeds':
			case 'migrations': 
				$path = $this->app->config->path.'src/Database/'.ucfirst($type).'/';
			break;

			case 'controllers':
			case 'models':
			case 'viewfilters':
				$path = $this->app->config->path.'src/'.ucfirst($type).'/';
			break;

			case 'config':
				$path = $this->app->config->path.strtolower($type).'/';
			break;		
			
			default:
				return false;
			break;
		}

		foreach (glob($path.'*.php') as $key => $value)
		{	
			$value = str_replace([$this->app->config->path,'/'], ['App/' ,'\\'],str_replace(['src/' ,'.php'], '', $value));
			$value_exp = str_replace( $action,'', explode('\\', $value)[count( explode('\\', $value) )-1] );

			if(   ($class_name == 'all')   | (  strtolower($value_exp ) == strtolower( $class_name )  ) ){
				 array_push($return, $value);
			}
		}

		if ($return) {
			return $return;
		}

		return false;
	}






	public function migrate($command){

		$title = '';
		$response = [];

		function setresp($resp, $response = []){
			foreach($resp as $r){
				array_push($response, $r );
			}
			return $response;
		}

		if ( !is_null($command) && ( count( explode( ':',$command) ) > 1 ) ) {

			$command_exp = explode( ':',$command);
			$title .= 'Running '.ucfirst($command_exp[0]).' Tables!';

			switch ($command_exp[0]) {
				case 'create':
				case 'drop':
					$response = setresp($this->migrator( strtolower($command_exp[0]) ,$this->get_classes('Migrations', $command_exp[1])) , $response );
				break;

				case 'reset':
					$response = setresp($this->migrator('drop' ,$this->get_classes('Migrations', $command_exp[1])), $response );
					$response = setresp($this->migrator('create' ,$this->get_classes('Migrations', $command_exp[1])) , $response);
				break;	

				case 'seed':

					$classes = $this->get_classes('Seeds', $command_exp[1]);
	
					if ( $classes ) {
						$count = (count($classes) > 1 ) ? count($classes) : 1  ;
					   for($i=0; $i < $count; $i++){
							$args_name = strtolower(str_replace('Seeder',null,explode('\\', $classes[$i])[count(explode('\\', $classes[$i]))-1]) );
							if($this->seeder_objects){
								$response = setresp($this->seed($classes[$i] , $this->seeder_objects->$args_name), $response ) ;
							}else{
								array_push($response, 'Variable seeder_objects no is set!' );
							}
						}
						
					}else{
						array_push($response, 'The '.ucfirst($command_exp[1]).
						' Seeder Class  does not exist in database!');
					}

				break;

				case 'spoon' :
					 $classes = $this->get_classes('migrations', $command_exp[1] );
					
					 if ($classes) {
						$tables = [];
						foreach ($classes as $class) {
							$class_obj = new $class();
							if($class_obj){
								array_push($tables, $class_obj );
							}
						} 

						$response = setresp($this->spoon($tables, $this->spoon_flag ), $response );

					 }else{
						array_push($response, 'The '.ucfirst($command_exp[1]).
						 ' table does not exist in database!');
					 }	

				break;			
				
				default:
					$title = 'Help';
					array_push($response, 'Usage:  /maker/migrate/create|drop|reset|seed[:table_name|all]');
				break;
			}

			

		}
		else{
			$title = 'Help';
			array_push($response, 'Usage:  /maker/migrate/create|drop|reset|seed[:table_name|all]');
		}
		
		return ['title' => $title, 'subtitle' => $response ];

   }





	private function migrator($type, $classes){ 

	 $msg=[]; $rs=null; $response=[];

	 if($classes) {	

	   foreach ($classes as $key => $class) {
		  $table_name = strtolower( str_replace( 'Migration', '', array_slice(explode('\\', $class) , -1 )[0]  ) );
		  $table = new Table($table_name);

			switch (strtolower($type)) {
				case 'create':
			
					    if ( !$table->exists() ) {
							unset($table);
							$migration = class_exists($class) ?  new $class() : false;

							if ($migration) {

								if($migration->create()){
									$msg = 1;
								}else{
									$msg = 4;
								}
								break;

							}else{
								$msg = 4;
							} 

						}else{
							$msg = 2;
						}
					
					break;

				case 'drop':
						
						if ($table->exists()) {
								$rs = $table->drop(); 

								if ($rs){
									$msg =1;
								}else{	
									$msg = 4;
								}		
								break;

						}else{
							$msg = 3;
						}

				 break;
								
				default:
					$msg = 5;
				break;
			}

		
			switch ($msg) {

				case 1:
					array_push($response, 'Table '.strtolower($table_name).' '.strtolower($type).' Successfully!');
				break;

				case 2:
					array_push($response, 'The '.strtolower($table_name).' Table already exists in the Database!');
				break;

				case 3:
					array_push($response,'The Table '.strtolower($table_name).' no exists in the Database or Class Table no is defined in App/Database/'.
					str_replace(' ' , '_', ucwords( str_replace('_' , ' ', strtolower($table_name) ) ) ) .'Migration.php!');
				break;

				case 4:
					array_push($response, 'An error occurred while '.strtolower($type).' table '.strtolower($table_name).'!');	
				break;

				case 5:
					array_push($response, 'Usage Command: /maker/migrate/[create|drop|reset|seed|spoon:[table_name|all]');
	   			break;
				
			}

		}

	}
	else{

		array_push($response, 'The Table no exists in the Database!');
	}

	
	return $response; 
	
	}



	public function seed($classes, $args= null){

		$class_obj=null; $response=[]; $name='';

		if ($classes) {
			$count = ( is_array($classes) && (count($classes) > 1) ) ? count($classes) : 1;

			for ( $i=0; $i < $count; $i++ ) {
				$class = ( is_array($classes) && (count($classes) > 1) ) ?  $classes[$i] : $classes;
				$class_obj = class_exists( $class ) ? new $class($args): false ;
				$response = setresp($class_obj->get_response(), $response );
		   }

			return $response;
		}else{
			array_push($response, 'The '.$name.' class does not exist in database!');
		}
		return $response;

	}




	public function spoon(array $migrations, $flag = '##teste##' ){
		$response = [];

		if(count($migrations ) >= 1){
			
			foreach ($migrations as $migration ) {
				$class = 'App\\Models\\'.$migration->class;
				$class_obj = new $class();
				$search = []; 	$results = [];
				
				foreach( $migration->table->getCols() as $i => $col ){
					array_push($search,  $col['name']);
				}
				array_push($search, $flag);
				array_push($results, $class::db()->search($search));

				if($results[0]) {
					foreach($results as $obj){
						$id = $obj->id;
						if ($obj->delete() ){
							array_push($response, 'Deleting '.ucfirst($migration->table->name)." item id:".$id."!");
						}
					}
				}else{
					array_push($response, 'No Found Test Resgisters in the table '.ucfirst($migration->table->name).'!');
				}

			}
		}
		

		return $response;

	}	




	public function file($data,$replace=[[],[]]){

			$usage = 'Usage: /maker/file/[controller|model|seeder|migration]:[class_name]|route:[app|api:file_name ]|'.
			'config:[middlewares|db|key|app]';

			$rs=null; $response = []; $continue = false; $explode = explode( ':', $data); 
			$command = isset($explode[0]) ? $explode[0] : false ;
			$subcommand= isset($explode[1]) ? $explode[1] : false ;
			$subcommand2= isset($explode[2]) ? $explode[2] : false ;

			$type_exists = function($this_command ,  $this_subcommand , $this_makefile ){	
				return  isset( $this_makefile->templates->$this_command->type ) && ( $this_subcommand && isset( $this_makefile->templates->$this_command->type->$this_subcommand )) ;
			};

			$title = 'Running Make File '.ucfirst($explode[0]).'!';	

			$templates_path =  $this->path.'templates/';
			$makefile = json_decode(file_get_contents($this->app->config->vendor_path.'Maker/maker.json') );
			$template = $templates_path;
			$filename = $this->app->config->path;
			
			if( $command && isset( $makefile->templates->$command )  ){

				if( $subcommand  ){

					foreach( array('template', 'filename' ) as $item ) {

						$subitem = '';
							switch ($item) {
								case 'template':
									$subitem = 'src';
								break;

								case 'filename':
									$subitem = 'path';
								break;
			
							}

							if( $type_exists($command, $subcommand, $makefile) && isset(  $makefile->templates->$command->type->$subcommand->$subitem )  ) {
								$$item .= $makefile->templates->$command->type->$subcommand->$subitem;
							}else{
								if(isset( $makefile->templates->$command->$subitem  )){
										$$item .= $makefile->templates->$command->$subitem;
								}else{
									$$item = false;
								}
							}
						

					}

					if( $filename && $template ){
						$cocat_name = '';		
							
						if ( $subcommand2 && in_array($command, array('route','config' )) ){
							$cocat_name = $subcommand2;
						}elseif( !isset($makefile->templates->$command->type) ){
							$cocat_name = $subcommand;
						}elseif( isset($makefile->templates->$command->type->$subcommand ) ) {
							$cocat_name = $subcommand;
						}else{
							$filename = false;
						}

						if( $filename){
							$filename .=  str_replace('_' , '', !in_array($command, array('route','config' ) ) ? ucwords($cocat_name , '_') : strtolower($cocat_name) )   .'.php';
							$resp =  $this->makefile($filename ,$template, $replace);
							if(is_array($resp)) {
							   foreach($resp as $r){
								  array_push($response, $r);
							   }
						    }else {
							   array_push($response, $resp);
							}
						}
						elseif(is_string($resp)) {
							array_push($response,"Filename not isset!" );
						}			
						

					}else{
						array_push($response, "Template for $command '$subcommand' not fount !" );
					}


				}else{
					array_push($response, $usage);
				}	
			
			}else{
				array_push($response, $usage);
			}
			
		return [ 'title' => $title, 'subtitle' => $response ];	
	}





	private function makefile(string $filename, string $template , $replace=[[],[]] ) {

		if ( file_exists( $filename )  ) {
			return [false, 'The file "'.$filename.'" already exists!'];
		}else{
			if( is_writable(dirname($filename)) && $template ){	
				$tmp = fopen($template, 'r');
				$content = fread($tmp, filesize($template));
				$document = fopen($filename, 'a+');
				$rw = fwrite($document, '<?php ');
				$rw = fwrite($document , str_replace( array_merge( ['{{name}}'], $replace[0] ) , array_merge( [ ucwords( basename($filename, '.php' ), '_' ) ], $replace[1] )  ,$content) );
				fclose($document);	

				$chmod = chmod($filename,0775); 

				if (file_exists($filename)) {
					return "Creating $filename Successfully!" ;
				}

				return "An error occurred while creating file $filename!" ;
			}else{
				return [ "Permission denied for create file $filename! ",
			           'Execute on cli in the root directory: sudo chown user:root -R.' ];
			} 

		}

	}	


	

	public function show(String $subcommand){

		switch($subcommand){

			
			case 'models':
			case 'seeds':
			case 'migrations':
			case 'controllers':
			case 'config':
			case 'viewfilters':
				$type = ucfirst($subcommand);
			break;

			case 'tables':
				$type = ucfirst('migrations');
			break;

			default:
				$type = false;
			break;

		}

	

		if ( $type != false ) {
			$title = 'Running List '.ucfirst($subcommand);	
			$response =  '<ul>';

			foreach( $this->get_classes($type, 'all') as $class ){
				if( $type == 'Migrations') {
				 $obj = new $class();
				 $response .=  '<li style="color:'.(  $obj->exists() ? 'green' : 'brown' ).';" >';
				}else{
					$response .=  '<li style="color:green;" >';
				}

				$response .=  @end( explode('\\', $class ) );
				$response .=  '</li>';
			}
		}else{

			$title = 'Running Show List | Help';	
			$response =  '<ul>';

			$types = [
				'models',
				'seeds',
				'migrations',
				'controllers',
				'config',
				'viewfilters'
			];

			foreach($types as $type ){
				$response .=  '<li style="color:green;" > <a href="/maker/show/'.$type.'">'.$type.' </li>';
			}	

		}
		
		$response .=  '</ul>  ';
		return [ 'title' => $title, 'subtitle' =>  [$response]];

	}

	








}
