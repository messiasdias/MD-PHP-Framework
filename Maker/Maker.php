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
	private $app, $path, $response, $migrations=[], $seeds=[], $tables=null, $spoon_flag, $seeder_objects;

	public function __construct(App $app) {
		$this->app = $app;
		$this->path = $this->app->config->vendor_path.'Maker/';

		if( file_exists($this->app->config->path.'/config/maker.php' ) ){
			include $this->app->config->path.'/config/maker.php';
		}else{
			$this->seeder_objects = false;
			$this->spoon_flag = '##test##';
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
			case 'routers':
			case 'routers/api':
				$path = $this->app->config->path.'src/'.ucfirst( str_replace(':', '/', $type) ).'/';
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

			if( ($class_name == 'all')   |  (  strtolower($value_exp ) == strtolower( $class_name )  )  ){
				if(!App::validate($value_exp, 'endwith:.example')) {
					array_push($return, $value);
				}
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

		function setresp($resp, array &$response = []){
			foreach($resp as $r){
				array_push($response, $r );
			}
		}

		if ( !is_null($command) && ( count( explode( ':',$command) ) > 1 ) ) {

			$command_exp = explode( ':',$command);
			$title .= "Running ".ucfirst($command_exp[0]).' Tables!';

			switch ($command_exp[0]) {
				
				case 'create':
				case 'drop':
				case 'reset':	
					if( $command_exp[0] == 'reset'){
						setresp($this->migrator('drop' , $this->get_classes('Migrations', $command_exp[1])), $response );
						$command_exp[0] = 'create';
					}
					
					setresp($this->migrator(strtolower($command_exp[0]) , $this->get_classes('Migrations', $command_exp[1])) , $response );
				break;


				case 'seed':

					$classes = $this->get_classes('Seeds', $command_exp[1]);
	
					if ( $classes ) {
						$count = (count($classes) > 1 ) ? count($classes) : 1  ;
					   for($i=0; $i < $count; $i++){
							$args_name = strtolower(str_replace('Seeder',null,explode('\\', $classes[$i])[count(explode('\\', $classes[$i]))-1]) );

							if($this->seeder_objects && isset($this->seeder_objects->$args_name) ){
								setresp($this->seed($classes[$i] , (array) $this->seeder_objects->$args_name ), $response ) ;
							}else{
								array_push($response, ['Variable seeder_objects no is set!', 'error'] );
							}
						}
						
					}else{
						array_push($response, ['The '.ucfirst($command_exp[1]).
						' Seeder Class  does not exist in src/Database/Seeds/', 'warning']);
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

						setresp($this->spoon($tables, $this->spoon_flag), $response );

					 }else{
						array_push($response, ['The '.ucfirst($command_exp[1]).
						 ' table does not exist in database!', 'warning' ]);
					 }	

				break;			
				
				default:
					$title = 'Help';
					array_push($response, ['Usage:  /maker/migrate/create|drop|reset|seed[:table_name|all]', 'info' ]);
				break;
			}

			

		}
		else{
			$title = 'Help';
			array_push($response, ['Usage:  /maker/migrate/create|drop|reset|seed[:table_name|all', 'info']);
		}
		
		return [ 'title' => $title, 'subtitle' => $response ];
   }



	private function migrator($type, $classes){ 

	 $msg=[]; $rs=null; $response=[];

	 if($classes) {	

	   foreach ($classes as $key => $class) {
		  $table_name = strtolower( str_replace( 'Migration', '', array_slice(explode('\\', $class) , -1 )[0]  ) );
		  $table = new Table($table_name);

			if( $table->exists() && ($type == 'create' ) ){
				$msg = 2;
			}
			elseif( !$table->exists() && ($type == 'create' ) ){
				$migration = new $class() ?? false;
				$rs = $migration->create() ?? false;
				if($rs){
					$msg = 1;
				}else{
					$msg = 4;
				}
			}
			elseif( $table->exists() && ($type == 'drop' ) ){
				$rs = $table->drop() ?? false;
				if($rs){
					$msg = 1;
				}else{
					$msg = 4;
				}
			}
			elseif( !$table->exists() && ($type == 'drop' ) ){
				$msg = 3;
			}
			else{
				$msg = 5;
			}

			switch ($msg) {

				case 1:
					array_push($response, ['Table "'.strtolower($table_name).'" '.strtolower($type).' Successfully!', 'success']);
				break;

				case 2:
					array_push($response, ['The "'.strtolower($table_name).'" Table already exists in the Database!','warning' ]);
				break;

				case 3:
					array_push($response,['The Table "'.strtolower($table_name).'" no exists in the Database!', 'error']);
				break;

				case 4:
					array_push($response, ['An error occurred while '.strtolower($type).' table "'.strtolower($table_name).'"!','error']);	
				break;

				case 5:
					array_push($response, ['Usage Command: /maker/migrate/[create|drop|reset|seed|spoon:[table_name|all]', 'info']);
	   			break;
			}

		}

	}
	else{
		array_push($response,['The Class no is defined in src/Database/Migrations/' , 'error']);
	}

	
	return $response; 
	
	}



	public function seed($classes, $args= null){

		$class_obj=null; $response=[];

		if ($classes) {
			$count = ( is_array($classes) && (count($classes) > 1) ) ? count($classes) : 1;

			for ( $i=0; $i < $count; $i++ ) {
				$class = ( is_array($classes) && (count($classes) > 1) ) ?  $classes[$i] : $classes;
				$class_obj = class_exists( $class ) ? new $class($args): false ;
				$response = setresp($class_obj->get_response(), $response );
		   }

		}

		return $response;
	}




	public function spoon(array $migrations, $flag = '##test##' ){
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
			'config:[middlewares|db|key|app] | (viewfilter|filters)';

			$rs=null; $response = []; $continue = false; 
			$explode = explode( ':', $data); 
			$command =  $explode[0] ?? false ;
			$subcommand = $explode[1] ?? false ;
			$subcommand2 = $explode[2] ?? false ;

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
								  array_push($response, [$r, 'warning']);
							   }
						    }else {
							   array_push($response, [$resp, 'success']);
							}
						}
						elseif(is_string($resp)) {
							array_push($response, ["Filename not isset!", 'error'] );
						}			
						

					}else{
						array_push($response, ["Template for $command '$subcommand' not fount !", 'error'] );
					}


				}else{
					array_push($response, [$usage, 'info']);
				}	
			
			}else{
				array_push($response,[$usage, 'info']);
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
				return [ "Permission denied for create file $filename!",
			           'Execute on cli in the root directory: sudo chown user:root -R.' ];
			} 

		}

	}	


	private function listComposerScripts(){
		$scripts = json_decode( file_get_contents($this->app->config->path.'composer.json') )->scripts ?? false;
		$title = 'Running List Composer Scripts';
		$response = [];

		foreach( $scripts as $key => $script ){
			array_push($response, ["composer run ".$key, 'info']) ;
			$script = (array)  $script;
			foreach( $script as $item){
				array_push($response, [$item]);
			}
		}

		return [ 'title' => $title, 'subtitle' =>  $response];
	}




	public function show(String $command){
		$response = [];
		switch($command){

			case 'models':
			case 'seeds':
			case 'migrations':
			case 'controllers':
			case 'config':
			case 'viewfilters':
			case 'routes:app':		
			case 'routes:api':
			case 'routes':	
				$type = ucfirst(str_replace([':api', ':app'], ['/api',''], $command) );
			break;

			case 'filters':
				return $this->show('viewfilters');
			break;

			case 'tables':
				$type = ucfirst('migrations');
			break;

			case 'scripts':
			case 'composerscripts':
				return $this->listComposerScripts();
			break;

			default:
				$type = false;
			break;

		}


		if ( $type != false ) {
			
			$title = 'Running List '.ucfirst($command);

			if( in_array($type,  ['Migrations', 'Models', 'Seeds'] ) ) {
				foreach( $this->get_classes($type, 'all') as $class ){
					if( $type == 'Migrations') {
						$obj = new $class();
						array_push($response, [ ($obj->exists() ) ? $class." *" : $class." **" , $obj->exists() ? "success"  : 'warning'   ] );
					}else{
						array_push($response, [$class]);
					}

				}
			}

			if( in_array($type,  ['Config', 'Routes', 'Viewfilters'] ) ) {
				
				$path = $this->app->config->path;
				switch( strtolower($type) ){

					case 'config':
						$path .= "config"; 
					break;

					case 'routes:app':
					case 'routes:api':	
					case 'routes':
						$path .= "src/Routes"; 
						if( isset(explode(':', $type)[1]) && (explode(':', $type)[1] == 'api') ){
							$path .= "/api"; 
						}
					break;

					case 'viewfilters':
					case 'filters':
						$path .= "src/Viewfilters"; 
					break;
				}
				$path .= "/*.php";

				foreach( glob($path) as $file ){
					switch( strtolower($type) ){
						case 'config':
							$name = str_replace($this->app->config->path,'', $file);
							if(!App::validate($name, 'endWith:example.php')) {
								array_push($response, [$name]);
							}
						break;

						case 'filters':
						case 'viewfilters':
						case 'routes':
							$name = str_replace($this->app->config->path,'', $file);
							array_push($response, [ $name ]);
						break;
					}
				}
			}
			
			if( ($type == 'Migrations') && ($this->app->config->mode == 'console' ) ) {
				array_push($response, [ "\n\n* - migrations Created on database", 'success']);
				array_push($response, [ "** - migrations no is Created on database", 'warning']);
			}

		}else{
			$title = "Running Show List | Help \n";	
			$types = [
				'models',
				'seeds',
				'migrations',
				'controllers',
				'config',
				'viewfilters|filters',
				'routers[:api|:app]'
			];

			foreach($types as $type ){
				array_push($response, $type);
			}
		}
		
		return [ 'title' => $title, 'subtitle' =>  $response];

	}

	








}
