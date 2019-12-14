<?php
/**
 *  Galery_Comments_Reply Migration Class
 */

namespace App\Database\Migrations;
use App\Database\DB;
use App\Database\Table;
use App\Database\Migration;


class Galery_Comments_Reply extends Migration 
{

		public function cols(){
			// table cols
			// $this->table->addCol('col-name','col-type',col-size [100], NULL [false | true], AI [true|false]);
			$this->table->addCol('id','int',100, false, true);
			$this->table->addCol('author_id','int',100);
			$this->table->addCol('galery_id','int',1);
			$this->table->addCol('galery_item_id','int',100);
			$this->table->addCol('comment_id','int',100);	
			$this->table->addCol('text','text', 1000 );
			$this->table->addCol('created','timestamp');
			$this->table->addCol('updated','timestamp');

		}


}