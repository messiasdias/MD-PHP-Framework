<?php
namespace App\Database\Seeds;
use App\Database\Seeder;
use App\Models\Demo;
/**
 * Demos Seeder Class
 */
class Demos extends Seeder
{
	
	public function __construct($count){

		$i=1;
		while ( $i <= $count) {
				

			
			foreach (glob('../assets/public/img/teste/*') as $img) {

			   $img = str_replace('../assets/public', '', $img);
		
			   if ( $i <= $count ) {	
			   	
				$demo = new Demo();
				$demo->id = $i;	
				$demo->author_id = 1;	
				$demo->title = "Demo {Teste} Item ".$i ;  
				$demo->description = "Text Description of  Demo Teste Item ".$i;
				$demo->img =  $img ;
				$demo->link = "https://www.lipsum.com/";
				$demo->git = "https://www.lipsum.com/teste.git"; 
				$demo->publish = 1;     
				$this->set_response( $demo->create() , $demo->title);

				$i++;

			   }
			   else{
				break;
			   }

			} 


		}
			

	}









}



?>