<?php

/**
 *  Table Class
 */
namespace App\Database;


class Table 
{
		public $name, $cols=[];


		public function __construct($name){
			$this->name($name);
		}


		public function create(){
			$sql = "CREATE TABLE IF NOT EXISTS ".strtolower($this->name)." (".
			implode($this->cols, ', ').")ENGINE=MyISAM;";
			$db = new DB();
		    $rs = $db->conection()->query($sql);
		    if ($rs){
		    	return true;
		    }else{
		    	return false;
		    }
		}



		public function drop(){
			$sql = "DROP TABLE IF EXISTS ".$this->name.";" ;
			$db = new DB();
			$rs = $db->conection()->query($sql);

			if ($rs){
		    	return true;
		    }else{
		    	return false;
		    }
		}



		public function exists($table=null){
			$sql = "SHOW TABLES;"; 	$return=[]; 
			$db = new DB();
			$rs = $db->conection()->query($sql);
			//$return[0] = $rs->fetch(\PDO::FETCH_NUM);

			while ( $row = $rs->fetch(\PDO::FETCH_NUM) ) {
				array_push($return, $row[0]);
			}
			
			//var_dump($return); exit;

			if ( !is_null($table) ){
				if ( in_array($table, $return) ){
					return true;
				}
			}else{
		
				if ( in_array($this->name, $return) ){
					return true;
				}
			} 

			return false;
			
		}
		

		public function name($name = null){
			if ($name){
				$this->name = $name;
			}else{
				if (isset($this->name)){
					return $this->name;
				}
			}
		}



		public function addCol($name,$type,$tam=0,$null=false,$ai=false, $default=null){
			// $this->table->addCol('col-name','col-type',col-size [100], NULL [false | true], AI [true|false], DEFAULT ['value'] );

			 $sql = " ".strtolower($name)." ";
			 $sql .= (isset($type)) ? strtoupper($type) : strtoupper('varchar'); 
			 $tam = ( ($tam == 0) && ($type == 'varchar')) ? 255 : $tam ;
			 $sql.= "(".$tam.")";

			 if( is_array($default) ){
			 	$default = implode(",",$default);
			 }

			 $sql .= ($default) ? " DEFAULT '".$default."' ": '';

			 if ( strtoupper($type) == "TIMESTAMP") {
				  $null = false;  $ai = false;
			  	 if ( is_integer( strpos($name, 'create') ) ){ $sql.= " DEFAULT CURRENT_TIMESTAMP "; }
			  	 elseif (is_integer( strpos($name, 'update') ) ) {$sql.= " DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP "; }
			 }

			 $sql.= ($null) ? " NULL " : " NOT NULL " ;
			 $sql .= ($ai) ? ' AUTO_INCREMENT PRIMARY KEY ' : '' ;
			 array_push($this->cols, $sql);
					
		}


		













	
}












?>