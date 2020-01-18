<?php 
/**
 *  DB Class
 */

namespace App\Database;
use \PDO;
use App\App;
use App\Database\Table;


class DB {

	protected $path, $host, $port, $database, $user, $pass, $table, $class;


	public function __construct($class=null){

		$path = dirname($_SERVER['SCRIPT_FILENAME']);

		if( file_exists($path.'/../config/db.php')  ){
			include  $path.'/../config/db.php';  //Load DBConfigs
		}else{
			$makefile = strtolower( explode( '/', $_SERVER['SERVER_PROTOCOL'])[0] ).'://'.$_SERVER['SERVER_NAME'].'/maker/file/config:db';
			echo '<p style="color:brown;">File <b>config/db.php</b> not Found!</p>'.
			'<p> Click to <a href="'.$makefile.'" > Make File </a> or Send a HTTP/GET Request for '.$makefile.'</p>'.
			'<p> For Help: <a href="/maker" >Maker</p>' ;
			exit;
		}

		if( !is_null($class) && !App::validate($class, 'startwith:App\\Models\\' ) ){
			$class = 'App\\Models\\'.ucfirst($class);
		}
		

		if(  !is_null($class) && class_exists($class) ){
			$this->class = new $class();
			$this->table = $this->class->table;
		}else{
			$this->class = false;
			$this->table = false;
		}
		

	}


	public function conection(){
		try {
			$pdo = "mysql:host=".$this->host."; port=".$this->port."; dbname=".$this->database.";charset=utf8";
			
			if( (isset($this->host) && isset($this->port) ) && isset($this->database) ){
				return new PDO($pdo,$this->user,$this->pass);
			}else{
				echo '<p style="color:brown;">DB Config Not Found on <b>"../config/db.php"</b> !</p> ';
				exit;
			}
		  
		} catch(PDOException $error) {
		  echo 'PDO Error: '.$error;
		  exit;
		} 

	}



	public function select($select='*', array $orderby=['created', 'desc'] ){

		if( isset( $this->table ) ) {
			$sql = $this->sql_generator('select', $select , $orderby);
			$data = $this->return_select( $this->conection()->query($sql) );	

			if( !$data | isset($data->scalar) ) {
				return false;
			}
			
			return $data;
		}

		return false;
	}



	public function paginate($page = null , $per_page = null, $search=null){

		$start = 0; $end = 12;
		$total = $this->count();	

		if ( ($page == 'first') | ($page == 1) ){
			$page = 1;
			$start = 0;
			$end = isset($per_page) ? $per_page : 12;
		}
		elseif ($page == 'last'){

			$page = ceil($total/$per_page) ;
			$start = ( ($total - $per_page) < 0 ) ? 0 : ($total - $per_page);
			$end = $total+1;

		}elseif( $page >= 2 ){
			$start = ($page * $per_page) - $per_page; 
			$end = $per_page;  
		}

		if ( isset($this->table) ) {

			$search['start'] = $start; $search['end'] = $end;
			$sql = $this->sql_generator('paginate_and_search', $search);

			return ['page'=> $page,
					'pages' => ceil($total/$per_page),	 
					'count'=> $per_page, 
					'data' => $this->return_select( $this->conection()->query($sql) ) 
					];  

		} 

		return false;
	}


	public function search($search){
		return $this->paginate( 1, 100, $search);
	}



	private function return_select($rs) {

		if ($rs){

			if ( $this->class && class_exists( get_class($this->class) ) ) { 
				$rs->setFetchMode(PDO::FETCH_CLASS, get_class($this->class) );
			}else{
				$rs->setFetchMode(PDO::FETCH_ASSOC);
			}

			$return=[];
			
			if ($rs->rowCount() > 1){
			   while ( $row = $rs->fetch()) {
			    		array_push($return, $row );
			    	} 
			    	return $return;	
			}else {	
				return (object) $rs->fetch(); 
			} 

		}else{
			return false;
		}  

	}





	public function exists($id,$value){

		if ( $this->select([$id,$value]) ){
			return true;
		}else{
			return false;
		}
	}




	public function count(){
		if( $this->table ){

			$rs = $this->conection()->query('SELECT COUNT(*) FROM '.$this->table);
			if ($rs) {
				$rs->setFetchMode(PDO::FETCH_ASSOC);
				$total = (array)$rs->fetch()['COUNT(*)'];
				return (int) $total[0] ;
			}
		}

		return 0;
	}




	public function save( array $data, string $noexists = null){	
		$sql = null; $rs = -1; 
		if ( array_key_exists('id', $data) && $this->exists( 'id', $data['id']) ){
			$sql = $this->sql_generator('update', $data);
		}
		else
		{
			
			if ( !is_null($noexists) ){
				 foreach (explode(',', $noexists) as $noexists_key => $noexists_value) {
				 	if ( $this->exists($noexists_value, $data[$noexists_value]) ){
					  	return (object) ['status' => false, 'msg' => "Registration $noexists_value $data[$noexists_value] already exists on ".ucfirst($this->table).'!', 'data' => $data ];
				 	}
				 }
			 }	
				
			$sql = $this->sql_generator('insert', $data);
			
	
		}

		if ( !is_null($sql) ){
		  $rs = $this->conection()->exec($sql);
		}
		
		if ( $rs == 0){
			return (object) ['status' => false, 'msg' => 'No data has been edited!'];
		}
		elseif ( $rs >= 1 ){
			return (object) ['status' => true, 'msg' => 'Registration saved successfully!' , 'data' => $data ];
		}elseif($rs == -1){
			return (object) ['status' => false, 'msg' => 'Id Not Specified for Editing!', 'data' => $data ];
		}else{
			return (object) ['status' => false, 'msg' => 'An error occurred while saving data!', 'data' => $data ];
		}	

	}




	public function delete( array $data){

		if ( !$this->exists('id', $data['id']) ){
		  return (object) ['status' => false, 'msg' => 'Registration not exists on '.ucfirst($this->table).' !', 'data' => $data ];
		}
		else{

			$sql = $this->sql_generator('delete',$data);

			if  ( !is_null($sql) && ( $this->conection()->exec($sql) >= 1)){
				 return (object) ['status' => true, 'msg' => 'Registration deleted successfully in the '.ucfirst($this->table).'!', 'data' => $data ];

			}else{
				 return (object) ['status' => false, 'msg' =>  'An error occurred while deleting data in the '.ucfirst($this->table).' !', 'data' => $data ];
			}


		}
	}




	
	private function sql_generator($type='select', $data=null, array $orderby = null ) {
	
		$keys=null; $values=[]; $sql="";

		if( is_array($data) ){
			$keys = array_keys($data);
		}
		
		switch (strtolower($type)) {

			default:
			case 'select':
			//SELECT * FROM $this->table WHERE $id=$value;
				$sql = "SELECT * FROM ".$this->table;
				if ( isset($data) && is_array($data)  ){
					//SELECT * FROM portifolio.jobs where publish=1 and id=1 and author_id=3 and teste IS NULL;
					$sql .= " WHERE ";
					
					if(  is_array($data[0]) &&  is_array($data[1])  ){
						$for_sql = '';

						foreach( $data[0] as $key => $value ){
							$and = ( $key == ( count($data[0]) - 1 )  ) ? '' : ' and ';
							$for_sql .= $data[0][$key]."='".$data[1][$key]."' ".$and ;
						}
						$sql .= $for_sql;
					}else{
						$sql .= @$data[0]."='".@$data[1]."' ";
					}


					if( isset($data[2]) ){
						$for_sql = ' and ';
						foreach( $data[2] as $key => $value ){
							$and = ( $key == ( count($data[2]) - 1 )  ) ? '' : ' and ';
							$for_sql .= $data[2][$key]." IS NULL  ".$and ;
						}
						$sql .= $for_sql;
					}


				}elseif( is_string($data) ) {
			
					if( ($data == "*") | (strtolower($data) == "all") ){
						$sql .= "  WHERE 1";
					}elseif( count(explode(',', $data )) == 2 ){
						$sql .= " WHERE ".explode(',',$data)[0]."='".explode(',',$data)[1]."' ";
					}
				}


			
			
				//$sql .= ';';

			break;
			
			

			case 'insert':
			//INSERT INTO teste(key1, key2, key3, key4) VALUES (?,?,?,?)

				foreach ($data as $i => $value) {
					array_push($values, $value);
				}
				$sql = "INSERT INTO ".$this->table." (".implode($keys,", ").") VALUES ('".mb_convert_encoding(implode($values,"', '"), 'UTF-8')." ');";
				
				break;




			case 'update':
			//UPDATE teste SET key1 = ?, key2 = ?, key3 = ?, key4 = ? WHERE id = ?
					foreach ($data as $key => $value) {
						if ($key != 'id') {
							array_push($values, $key." = '".$value);
							}
					}	

					$sql = "UPDATE ".$this->table." SET ".mb_convert_encoding(implode($values, "', "), 'UTF-8')."' WHERE id = '".mb_convert_encoding($data['id'], 'UTF-8')."';";

				break;




			case 'delete':
			//DELETE FROM table WHERE id = ?
					$sql = "DELETE FROM ".$this->table."  WHERE id='".$data['id']."';";
				break;




			case 'paginate_and_search':
			//"SELECT * FROM table WHERE `colunm1` LIKE '%1%' or `colunm2` LIMIT 0,10;"
		
				$sql = "SELECT * FROM ".$this->table." WHERE  ";
				$start = $data['start']; unset($data['start']); 
				$end= $data['end']; unset($data['end']);

				if( !is_null($data) ){
					$find = '';
					if( is_array($data) && ( count($data) >= 2 ) ){
						$find = @end($data);
						unset( $data[count($data)-1]);
						$sql .= "`".implode( "` LIKE '%$find%' or `" ,$data)."` LIKE '%$find%' ";
					}else{
						$find = is_array($data) ? implode('', $data) : (string) $data ;
						$sql .= "`id` LIKE '%$find%' ";
					}
	
				}
				else{
					$sql .= ' 1 ';
				}
	
				$sql .= " LIMIT ".$start.",". $end." ";

				break;

		}


		if( 
			(( strtolower($type) == 'paginate_and_search') | ( strtolower($type) == 'select')) &&
			(isset($orderby) && ( (count($orderby) >= 1) | (count($orderby) == 2 )  ))
		){

			$sql .= 'ORDER BY ' .$orderby[0].' ';
			$sql .=  count($orderby) == 2  ? strtoupper($orderby[1]) : ' DESC' ;
			$sql .= ';';
		}	


		//var_dump($sql);

		return $sql;
			

	}








}






?>