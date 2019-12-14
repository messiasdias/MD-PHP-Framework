<?php 
/**
 *  DB Class
 */

namespace App\Database;
use \PDO;
use App\App;


class DB {

	private $host, $port, $database, $user, $pass;
	public $class;

	public function __construct(string $class ){
		include '../config/db.php'; //Load DBConfigs
		$this->class =  !is_null($class) ?  new $class() : false;
	}


	public function conection(){
		try {
			$pdo = "mysql:host=".$this->host."; port=".$this->port."; dbname=".$this->database.";charset=utf8";
			return new PDO($pdo,$this->user,$this->pass);
		  
		} catch(PDOException $error) {
		   return (object) ['status' => false, 'msg' => "PDOException error: $error", 'data' => NULL ];
		} 

	}




	public function select($select='*'){
		
		$sql = $this->sql_generator('select', $select);
		$data = $this->return_select( $this->conection()->query($sql) );	

		if( !$data | isset($data->scalar) ) {
			return false;
		}
		
		return $data;
	}



	public function paginate($page = null , $per_page = null, $search=null){
		
		$start = 0; $end = 12;
		$total = $this->count($this->class->table);	

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

		if ( isset($this->class->table) ) {

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
		$rs = $this->conection()->query('SELECT COUNT(*) FROM '.$this->class->table);
		if ($rs) {
			$rs->setFetchMode(PDO::FETCH_ASSOC);
			$total = (array)$rs->fetch()['COUNT(*)'];
			return (int) $total[0] ;
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
					  	return (object) ['status' => false, 'msg' => "Registration $noexists_value $data[$noexists_value] already exists on ".ucfirst($this->class->table).'!', 'data' => $data ];
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

		  return (object) ['status' => false, 'msg' => 'Registration not exists on '.ucfirst($this->class->table).' !', 'data' => $data ];
		}
		else{

			$sql = $this->sql_generator('delete',$data);

			if  ( !is_null($sql) && ( $this->conection()->exec($sql) >= 1)){
				 return (object) ['status' => true, 'msg' => 'Registration deleted successfully in the '.ucfirst($this->class->table).'!', 'data' => $data ];

			}else{
				 return (object) ['status' => false, 'msg' =>  'An error occurred while deleting data in the '.ucfirst($this->class->table).' !', 'data' => $data ];
			}


		}

		
	}




	
	private function sql_generator($type='select', $data=null ) {

		$keys=null; $values=[]; $sql="";

		if( is_array($data) ){
			$keys = array_keys($data);
		}

		switch (strtolower($type)) {

			default:
			case 'select':
			//SELECT * FROM $this->class->tables WHERE $id=$value
					$sql = "SELECT * FROM ".$this->class->table; 
					if ( isset($data) && is_array($data)  ){
						//SELECT * FROM portifolio.jobs where publish=1 and id=1 and author_id=3;
						$sql .= " WHERE ".$data[0]."='".$data[1]."' ";

						if( count( $data ) ){

						}


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
				$sql = "INSERT INTO ".$this->class->table." (".implode($keys,", ").") VALUES ('".mb_convert_encoding(implode($values,"', '"), 'UTF-8')." ');";
				
				break;




			case 'update':
			//UPDATE teste SET key1 = ?, key2 = ?, key3 = ?, key4 = ? WHERE id = ?
					foreach ($data as $key => $value) {
						if ($key != 'id') {
							array_push($values, $key." = '".$value);
							}
					}	

					$sql = "UPDATE ".$this->class->table." SET ".mb_convert_encoding(implode($values, "', "), 'UTF-8')."' WHERE id = '".mb_convert_encoding($data['id'], 'UTF-8')."'";

				break;




			case 'delete':
			//DELETE FROM table WHERE id = ?
					$sql = "DELETE FROM ".$this->class->table."  WHERE id='".$data['id']."'";
				break;




			case 'paginate_and_search':
			//"SELECT * FROM table WHERE `colunm1` LIKE '%1%' or `colunm2` LIMIT 0,10;"
		
				$sql = "SELECT * FROM ".$this->class->table." WHERE  ";
				$start = $data['start']; unset($data['start']); 
				$end= $data['end']; unset($data['end']);

				if( !is_null($data) ){
					$find = '';
					if( is_array($data) && ( count($data) >= 2 ) ){
						$find = $data[count($data)-1];
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
	
				$sql .= " LIMIT ".$start.",". $end.";";

				break;

		}

		return $sql;
			

	}








}






?>