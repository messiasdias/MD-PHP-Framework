<?php
/**
 *  Galery_Likes Migration Class
 */

namespace App\Database\Migrations;
use App\Database\DB;
use App\Database\Table;
use App\Database\Migration;


class Galery_Likes extends Migration 
{

		public function cols(){
			// table cols
			// $this->table->addCol('col-name','col-type',col-size [100], NULL [false | true], AI [true|false], DEFAULT );
			$this->table->addCol('id','int',100, false, true);
			$this->table->addCol('author_id','int',100);
			$this->table->addCol('galery_id','int',1, false, false);
			$this->table->addCol('galery_item_id','int',100);	
			$this->table->addCol('like_id','int', 1, false, false);
			$this->table->addCol('created','timestamp');
			$this->table->addCol('updated','timestamp');

		}


}