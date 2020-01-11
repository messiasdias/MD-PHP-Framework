<?php
/**
 *  Database Migration Class
 */

namespace App\Database;
use App\Database\Table;
use App\Database\DB;

/**
 * 
 */
class Migration 
{
	public $table, $class;
	
	/* Seting Table, if this not exists, create on construct Method*/
	public function __construct($tb=null){
		
		if (!isset($tb) | ($tb == '') ) {
		   $tb = strtolower( str_replace('Migration','', array_slice( explode('\\', get_called_class() ), 3  )[0] ) ) ;
		}

		$this->table = new Table(strtolower($tb));

	    if ($this->table) {	

			if ( !$this->table->exists() ) {
					  $this->cols();
				} 

	    }		

	}



	public function cols(){
			// table cols
			// $this->table->addCol('col-name','col-type',col-size [100], NULL [false | true], AI [true|false]);
	}

	public function create(){
	  if ( !$this->table->exists() ) {
			return $this->table->create();
		}else{
			return false;
		}
	}

	public function drop(){
	  if ( $this->table->exists() ) {
			return $this->table->drop();
		}else{
			return false;
		}
	}


	public function exists(){
		return $this->table->exists();
	}




}