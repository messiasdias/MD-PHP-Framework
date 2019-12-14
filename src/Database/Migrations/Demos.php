<?php
/**
 *  Demos Migration Class
 */

namespace App\Database\Migrations;
use App\Database\DB;
use App\Database\Table;
use App\Database\Migration;


class Demos extends Migration 
{

		public function cols(){
			// table cols
			// $this->table->addCol('col-name','col-type',col-size [100], NULL [false | true], AI [true|false]);
			$this->table->addCol('id','int',100, false, true);
			$this->table->addCol('author_id','int',100, true);		
			$this->table->addCol('title','varchar');
			$this->table->addCol('link','varchar');
			$this->table->addCol('git','varchar');
			$this->table->addCol('img','varchar');
			$this->table->addCol('description','text', 1000 , true);
			$this->table->addCol('publish','int',0,true);
			$this->table->addCol('created','timestamp');
			$this->table->addCol('updated','timestamp');

		}


}