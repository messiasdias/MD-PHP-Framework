<?php
/**
 *  Database Table Class
 */
namespace App\Database;
use App\App;
use App\Database\DB;


class Table 
{
		public $name;
		private $cols_sql=[], $cols=[];


		public function __construct(String $name=null ){
			if( !is_null($name) && is_string($name) ){
				$this->name = $name;
			}else{
				return false;
			}
		}

		private function db($class=null){
			return App::db($class);
		}

		public function getCols(){
			return $this->cols;
		}


		public function create(){
			$sql = "CREATE TABLE IF NOT EXISTS ".strtolower($this->name)." (".
			implode($this->cols_sql, ', ').")ENGINE=MyISAM;";
		    if ($this->db()->conection()->query($sql)){
		    	return true;
		    }else{
		    	return false;
		    }
		}



		public function drop(){
			$sql = "DROP TABLE IF EXISTS ".$this->name.";" ;
			if ($this->db()->conection()->query($sql)){
		    	return true;
		    }else{
		    	return false;
		    }
		}



		public function exists(){
			$sql = "SHOW TABLES;"; 	$return=[]; 
			$rs = $this->db()->conection()->query($sql);

			while ( $row = $rs->fetch(\PDO::FETCH_NUM) ) {
				array_push($return, $row[0]);
			}
		
			if ( in_array( $this->name , $return) ){
				return true;
			}

			return false;
		}
		

		


		public function addCol($name, $type = 'varchar' ,$size=0,$is_null=false,$ai=false, $default=null){
			// table cols
			// $this->table->addCol('col-name','col-type',col-size [100], NULL [false | true], AI [true|false], DEFAULT );
			 $col = [
				'name' => strtolower($name),
				'type' => $type,
				'size' => $size,
				'ai' => $ai,
				'null' => $is_null,
				'default' => $default,
			 ];

			 $sql = " ".strtolower($name)." ";
			 $sql .= (isset($type)) ? strtoupper($type) : strtoupper('varchar'); 
			 $size = ( ($size == 0) && ($type == 'varchar')) ? 255 : $size ;
			 $sql.= "(".$size.")";

			 if( is_array($default) ){
			 	$default = implode(",",$default);
			 }

			 $sql .= ($default) ? " DEFAULT '".$default."' ": '';

			 if ( strtoupper($type) == "TIMESTAMP") {
				  $is_null = false;  $ai = false;
			  	 if ( is_integer( strpos($name, 'create') ) ){ $sql.= " DEFAULT CURRENT_TIMESTAMP "; }
			  	 elseif (is_integer( strpos($name, 'update') ) ) {$sql.= " DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP "; }
			 }

			 $sql.= ($is_null) ? " NULL " : " NOT NULL " ;
			 $sql .= ($ai) ? ' AUTO_INCREMENT PRIMARY KEY ' : '' ;

			 array_push($this->cols_sql, $sql);
			 array_push($this->cols, $col);
					
		}


		













	
}












?>