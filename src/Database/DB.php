<?php 
/**
 *  DB Class
 */

namespace App\Database;
use \PDO;
use App\App;


class DB {

	private $host, $port, $database, $user, $pass;

	public function __construct(){
		include '../config/db.php'; //Load DBConfigs
	}


	public function conection(){
		try {
			$pdo = "mysql:host=".$this->host."; port=".$this->port."; dbname=".$this->database.";charset=utf8";
			return new PDO($pdo,$this->user,$this->pass);
		  
		} catch(PDOException $error) {
		   return (object) ['status' => false, 'msg' => "PDOException error: $error", 'data' => NULL ];
		} 

	}




	public function select(String $table, $select='*'){
		
		$sql = $this->sql_generator('select', $table, $select);
		$rs = $this->conection()->query($sql); 
		$data = $this->return_select($rs, $table);	

		if( !$data ) {
			return false;
		}

		return $data;
	}



	public function paginate($table, $page = null , $per_page = null){
		
		$start = 0; $end = 12;
		$total = $this->count($table);	

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

		if ( isset($table) ) {

			$sql = 'SELECT * FROM '.$table.' LIMIT '.$start.','. $end;
			$result = $this->conection()->query($sql);

			return ['page'=> $page,
					'pages' => ceil($total/$per_page),	 
					'count'=> $per_page, 
					'data' => $this->return_select($result, $table) 
					];  


		} 

		return false;
	}




	private function return_select($rs, $table) {
		$class = 'App\Models\\'.ucfirst( implode(array_slice( str_split($table) ,0 ,-1 ) , '') );
		if ($rs){

			if ( class_exists($class) ) { 
				$rs->setFetchMode(PDO::FETCH_CLASS, $class);
			}else{
				$rs->setFetchMode(PDO::FETCH_ASSOC);
				return false;
			}

			$return=[];
			
			if ($rs->rowCount() > 1){
			   while ( $row = $rs->fetch()) {
			    		array_push($return, $row );
			    	} 

			    	return $return;	
				
			}else {	
				return $rs->fetch(); 
			} 

		}else{
			return false;
		}  

	}





	public function exists($table,$id,$value){
		if ( $this->select($table, [$id,$value] ) ){
			return true;
		}else{
			return false;
		}
	}




	public function count($table){
		$rs = $this->conection()->query('SELECT COUNT(*) FROM '.$table);
		if ($rs) {
			$rs->setFetchMode(PDO::FETCH_ASSOC);
			$total = (array)$rs->fetch()['COUNT(*)'];
			return (int) $total[0] ;
		}
		return 0;
	}




	public function save($table, array $data, string $noexists = null){	
		$sql = null; $rs = -1;
		if ( array_key_exists('id', $data) && $this->exists($table, 'id', $data['id']) ){
			$sql = $this->sql_generator('update', $table, $data);
		}
		else
		{
			
			if ( !is_null($noexists) ){
				 foreach (explode(',', $noexists) as $noexists_key => $noexists_value) {
				 	if ( $this->exists($table,$noexists_value, $data[$noexists_value]) ){
					  	return (object) ['status' => false, 'msg' => "Registration $noexists_value $data[$noexists_value] already exists on ".ucfirst($table).'!', 'data' => $data ];
				 	}
				 }
			 }	
				
			$sql = $this->sql_generator('insert', $table, $data);	
	
		}

		if ( !is_null($sql) ){
		  $rs = $this->conection()->exec($sql);
		}
		
		if ( $rs == 0){
			return (object) ['status' => true, 'msg' => 'No data has been edited!'];
		}
		elseif ( $rs >= 1 ){
			return (object) ['status' => true, 'msg' => 'Registration saved successfully!' , 'data' => $data ];
		}elseif($rs == -1){
			return (object) ['status' => false, 'msg' => 'Id Not Specified for Editing!', 'data' => $data ];
		}else{
			return (object) ['status' => false, 'msg' => 'An error occurred while saving data!', 'data' => $data ];
		}	

	}




	public function delete($table, array $data){

		$sql =''; $rs=0;

		if ( !$this->exists($table, 'id', $data['id']) ){

		  return (object) ['status' => false, 'msg' => 'Registration not exists on '.ucfirst($table).' !', 'data' => $data ];
		}
		else{

			$sql = $this->sql_generator('delete', $table, $data);

			if ( !is_null($sql) ){
		 	 $rs = $this->conection()->exec($sql);
			}

			if ($rs >= 1){

				 return (object) ['status' => true, 'msg' => 'Registration deleted successfully in the '.ucfirst($table).'!', 'data' => $data ];

			}else{

				 return (object) ['status' => false, 'msg' =>  'An error occurred while deleting data in the '.ucfirst($table).' !', 'data' => $data ];
			}


		}


			

		
	}




	
	private function sql_generator($type='select',$table, $data=null ) {

		$keys=null; $values=[]; $sql="";

		if( is_array($data) ){
			$keys = array_keys($data);
		}

		switch (strtolower($type)) {

			default:
			case 'select':
			//SELECT * FROM $tables WHERE $id=$value
					$sql = "SELECT * FROM ".$table; 
					if ( isset($data) && is_array($data)  ){
						$sql .= " WHERE ".$data[0]."='".$data[1]."' ";
					}elseif( is_string($data) ) {
			
						if( ($data == "*") | (strtolower($data) == "all") ){
							$sql .= "  WHERE 1";
						}elseif( count(explode(',', $data )) == 2 ){
							$sql .= " WHERE ".explode(',',$data)[0]."='".explode(',',$data)[1]."' ";
						}
					}
			
					$sql .= ';';

			break;
			
			

			case 'insert':
			//INSERT INTO teste(key1, key2, key3, key4) VALUES (?,?,?,?)

				foreach ($data as $i => $value) {
					array_push($values, $value);
				}
				$sql = "INSERT INTO ".$table." (".implode($keys,", ").") VALUES ('".mb_convert_encoding(implode($values,"', '"), 'UTF-8')." ');";
				
				break;


				case 'update':
				//UPDATE teste SET key1 = ?, key2 = ?, key3 = ?, key4 = ? WHERE id = ?
					foreach ($data as $key => $value) {
						if ($key != 'id') {
							array_push($values, $key." = '".$value);
							}
					}	

					$sql = "UPDATE ".$table." SET ".mb_convert_encoding(implode($values, "', "), 'UTF-8')."' WHERE id = '".mb_convert_encoding($data['id'], 'UTF-8')."'";

				break;


				case 'delete':
				//DELETE FROM table WHERE id = ?
					$sql = "DELETE FROM ".$table."  WHERE id='".$data['id']."'";
				break;

		}

		return $sql;
			

	}








}






?>